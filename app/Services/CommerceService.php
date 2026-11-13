<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\DepositRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Prisoner;
use App\Models\PrisonerLedgerEntry;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommerceService
{
    public function create(Customer $customer, string $kind, array $data, string $key): Payment
    {
        $hash = hash('sha256', json_encode([$kind, $data]));

        return DB::transaction(function () use ($customer, $kind, $data, $key, $hash) {
            // Serialize requests from the same customer, including concurrent retries.
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $existing = Payment::where('customer_id', $customer->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                abort_unless(hash_equals($existing->request_hash, $hash), 409, 'Idempotency-Key đã được dùng cho yêu cầu khác.');

                return $existing;
            }
            foreach (['bank', 'account_number', 'account_name', 'webhook_key'] as $field) {
                abort_unless(config("commerce.$field"), 503, 'Chưa cấu hình thanh toán ngân hàng.');
            }
            $prisoner = Prisoner::published()->findOrFail($data['prisoner_id']);
            $normalize = fn ($phone) => preg_replace('/^84/', '0', preg_replace('/\D/', '', (string) $phone));
            $phone = $normalize($customer->phone);
            abort_unless($phone && collect($prisoner->phones ?? [])->contains(fn ($entry) => $normalize($entry['phone'] ?? '') === $phone), 403, 'Bạn không được phép gửi tiền hoặc đặt hàng cho phạm nhân này.');

            $attributes = ['customer_id' => $customer->id, 'prisoner_id' => $prisoner->id];
            if ($kind === 'order') {
                $items = [];
                $amount = 0;
                foreach ($data['items'] as $item) {
                    $product = Product::where('is_active', true)->lockForUpdate()->find($item['product_id']);
                    if (! $product) {
                        throw ValidationException::withMessages(['items' => 'Mặt hàng không còn được bán.']);
                    }
                    $amount += $product->price * $item['quantity'];
                    $items[] = ['product_id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'quantity' => $item['quantity']];
                }
                if ($amount < 1 || $amount > 100000000) {
                    throw ValidationException::withMessages(['items' => 'Tổng đơn phải từ 1 đến 100.000.000 VNĐ.']);
                }
                $record = Order::create($attributes + ['total' => $amount, 'note' => $data['note'] ?? null]);
                $record->items()->createMany($items);
            } else {
                $amount = $data['amount'];
                $record = DepositRequest::create($attributes + ['amount' => $amount]);
            }

            return Payment::create([
                'customer_id' => $customer->id,
                'order_id' => $kind === 'order' ? $record->id : null,
                'deposit_request_id' => $kind === 'deposit' ? $record->id : null,
                'code' => ($kind === 'order' ? 'DH' : 'LK').strtoupper(Str::random(20)),
                'amount' => $amount, 'bank' => config('commerce.bank'),
                'account_number' => config('commerce.account_number'), 'account_name' => config('commerce.account_name'),
                'expires_at' => now()->addMinutes(max(1, config('commerce.payment_minutes'))),
                'idempotency_key' => $key, 'request_hash' => $hash,
            ]);
        }, 3);
    }

    public function receive(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $transaction = BankTransaction::firstOrCreate(['sepay_id' => (string) $payload['id']], [
                'amount' => $payload['transferAmount'], 'status' => 'RECEIVED', 'payload' => $payload,
            ]);
            $transaction = BankTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($transaction->status !== 'RECEIVED') {
                return;
            }
            $code = $payload['code'] ?? null;
            if (! $code && preg_match_all('/\b(?:DH|LK)[A-Z0-9]{20}\b/i', $payload['content'] ?? '', $matches) === 1) {
                $code = strtoupper($matches[0][0]);
            }
            $payment = $code ? Payment::where('code', strtoupper($code))->lockForUpdate()->first() : null;
            $reason = match (true) {
                $payload['transferType'] !== 'in' => 'NOT_INCOMING',
                ! $payment => 'UNKNOWN_PAYMENT',
                default => null,
            };
            if ($payment) {
                $transaction->payment_id = $payment->id;
                $reason ??= match (true) {
                    $payload['accountNumber'] !== $payment->account_number => 'WRONG_ACCOUNT',
                    ! empty($payload['subAccount']) => 'UNSUPPORTED_SUB_ACCOUNT',
                    $payment->status !== 'PENDING' => 'PAYMENT_ALREADY_PROCESSED',
                    $payment->expires_at->isPast() => 'PAYMENT_EXPIRED',
                    (int) $payload['transferAmount'] !== $payment->amount => 'AMOUNT_MISMATCH',
                    default => null,
                };
            }
            if ($reason) {
                $transaction->fill(['status' => 'REVIEW', 'reason' => $reason])->save();
                if ($payment && $payment->status === 'PENDING' && ! in_array($reason, ['NOT_INCOMING', 'WRONG_ACCOUNT', 'UNSUPPORTED_SUB_ACCOUNT'])) {
                    $payment->update(['status' => 'REVIEW']);
                }

                return;
            }
            $payment->update(['status' => 'PAID', 'paid_at' => now()]);
            if ($payment->order_id) {
                $payment->order()->update(['status' => 'PAID']);
            } else {
                $deposit = $payment->depositRequest()->lockForUpdate()->firstOrFail();
                PrisonerLedgerEntry::create([
                    'prisoner_id' => $deposit->prisoner_id, 'deposit_request_id' => $deposit->id,
                    'bank_transaction_id' => $transaction->id, 'amount' => $payment->amount,
                ]);
                $deposit->update(['status' => 'CREDITED']);
            }
            $transaction->fill(['status' => 'MATCHED', 'reason' => null])->save();
        }, 3);
    }

    public function paymentData(Payment $payment): array
    {
        $data = $payment->toArray();
        $data['qr_url'] = 'https://qr.sepay.vn/img?'.http_build_query([
            'bank' => $payment->bank, 'acc' => $payment->account_number,
            'amount' => $payment->amount, 'des' => $payment->code,
        ]);
        $data['is_expired'] = $payment->status === 'PENDING' && $payment->expires_at->isPast();

        return $data;
    }
}
