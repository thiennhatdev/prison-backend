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
            $normalize = fn ($phone) => preg_replace('/^84/', '0', preg_replace('/\D/', '', (string) $phone));
            $phone = $normalize($customer->phone);
            if ($normalize($data['sender']['phone']) !== $phone) {
                throw ValidationException::withMessages(['sender.phone' => 'Số điện thoại người gửi phải khớp tài khoản đăng nhập.']);
            }

            $recipient = $data['recipient'];
            $candidates = Prisoner::published()->where('prisoner_code', $recipient['prisoner_code'])->get();
            if ($candidates->isEmpty()) {
                throw ValidationException::withMessages(['recipient.prisoner_code' => 'Không tìm thấy phạm nhân có số giam này.']);
            }
            $lastName = fn (string $name) => mb_strtolower(Str::of($name)->trim()->afterLast(' ')->toString());
            $candidates = $candidates->filter(fn (Prisoner $prisoner) => $lastName($recipient['name']) === $lastName((string) $prisoner->username));
            if ($candidates->isEmpty()) {
                throw ValidationException::withMessages(['recipient.name' => 'Họ tên không khớp số giam đã nhập.']);
            }
            $candidates = $candidates->filter(fn (Prisoner $prisoner) => $phone && collect($prisoner->phones ?? [])
                ->contains(fn ($entry) => $normalize($entry['phone'] ?? '') === $phone));
            abort_unless($candidates->isNotEmpty(), 403, 'Bạn không được phép gửi tiền hoặc đặt hàng cho phạm nhân này.');
            if ($candidates->count() > 1) {
                throw ValidationException::withMessages(['recipient.prisoner_code' => 'Có nhiều phạm nhân trùng thông tin. Vui lòng liên hệ quản trị viên.']);
            }
            $prisoner = $candidates->first();

            $attributes = [
                'customer_id' => $customer->id,
                'prisoner_id' => $prisoner->id,
                'sender_details' => $data['sender'] ?? null,
                'recipient_details' => $data['recipient'] ?? null,
            ];
            if ($kind === 'order') {
                $items = [];
                $amount = 0;
                foreach ($data['items'] as $item) {
                    $product = Product::where('is_active', true)
                        ->whereHas('productCategory', fn ($query) => $query->where('is_active', true))
                        ->lockForUpdate()->find($item['product_id']);
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
            $codes = [];
            if (preg_match('/^(?:DH|LK)[A-Z0-9]{6,20}$/i', (string) ($payload['code'] ?? ''))) {
                $codes[] = strtoupper($payload['code']);
            }
            preg_match_all('/\b(?:DH|LK)[A-Z0-9]{6,20}\b/i', $payload['content'] ?? '', $matches);
            foreach ($matches[0] as $code) {
                $codes[] = strtoupper($code);
            }
            $codes = array_unique($codes);
            // Older clients may have used the order's idempotency key as the transfer memo.
            $payments = Payment::where(function ($query) use ($codes) {
                $query->whereIn('code', $codes)->orWhereIn('idempotency_key', $codes);
            })->lockForUpdate()->get();
            $payment = $payments->count() === 1 ? $payments->first() : null;
            $reason = match (true) {
                $payload['transferType'] !== 'in' => 'NOT_INCOMING',
                $payments->count() > 1 => 'AMBIGUOUS_PAYMENT',
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

    public function reconcileOrder(int $transactionId, int $orderId, int $reviewerId, string $note): void
    {
        DB::transaction(function () use ($transactionId, $orderId, $reviewerId, $note) {
            $transaction = BankTransaction::whereKey($transactionId)->lockForUpdate()->firstOrFail();
            abort_unless($transaction->status === 'REVIEW' && $transaction->reason === 'UNKNOWN_PAYMENT'
                && $transaction->payment_id === null, 409, 'Giao dịch không còn chờ ghép thủ công.');

            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $payment = Payment::where('order_id', $order->id)->lockForUpdate()->firstOrFail();
            abort_unless($order->status === 'AWAITING_PAYMENT' && $payment->status === 'PENDING'
                && ! BankTransaction::where('payment_id', $payment->id)->exists(), 409, 'Đơn đã có giao dịch hoặc không còn chờ thanh toán.');

            $payload = $transaction->payload;
            abort_unless(($payload['transferType'] ?? null) === 'in'
                && empty($payload['subAccount'])
                && ($payload['accountNumber'] ?? null) === $payment->account_number
                && (int) ($payload['transferAmount'] ?? 0) === $payment->amount,
                409, 'Tài khoản nhận hoặc số tiền không khớp đơn hàng.');

            $payment->update(['status' => 'PAID', 'paid_at' => now()]);
            $order->update(['status' => 'PAID']);
            $transaction->update([
                'payment_id' => $payment->id, 'status' => 'MATCHED', 'reason' => 'MANUALLY_MATCHED',
                'reconciled_by' => $reviewerId, 'reconciled_at' => now(), 'reconciliation_note' => $note,
            ]);
        }, 3);
    }

    public function paymentData(Payment $payment): array
    {
        $data = $payment->toArray();
        $transferContent = in_array(strtoupper(trim($payment->bank)), ['ICB', 'VIETINBANK', '970415'], true)
            ? 'SEVQR '.$payment->code : $payment->code;
        $data['transfer_content'] = $transferContent;
        $data['qr_url'] = 'https://qr.sepay.vn/img?'.http_build_query([
            'bank' => $payment->bank, 'acc' => $payment->account_number,
            'amount' => $payment->amount, 'des' => $transferContent,
        ]);
        $data['is_expired'] = $payment->status === 'PENDING' && $payment->expires_at->isPast();

        return $data;
    }
}
