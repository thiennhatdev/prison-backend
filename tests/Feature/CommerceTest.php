<?php

namespace Tests\Feature;

use App\Http\Middleware\LogAccess;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Prisoner;
use App\Models\Product;
use App\Services\CommerceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('phone');
            $t->string('role')->default('CUSTOMER');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('prisoners', function (Blueprint $t) {
            $t->id();
            $t->boolean('published')->default(true);
            $t->json('phones');
            $t->integer('position')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        (require database_path('migrations/2026_11_13_000000_create_commerce_tables.php'))->up();
        config(['commerce.bank' => 'MB', 'commerce.account_number' => '12345', 'commerce.account_name' => 'TEST', 'commerce.webhook_key' => 'secret']);
        $this->withoutMiddleware(LogAccess::class);
    }

    private function customer(): Customer
    {
        return Customer::create(['phone' => '0901234567', 'role' => 'CUSTOMER', 'is_active' => true]);
    }

    private function prisoner(): Prisoner
    {
        return Prisoner::withoutEvents(fn () => Prisoner::create(['published' => true, 'phones' => [['phone' => '0901234567']]]));
    }

    private function payload(Payment $payment, int $id = 1): array
    {
        return ['id' => $id, 'accountNumber' => '12345', 'transferType' => 'in', 'transferAmount' => $payment->amount, 'code' => $payment->code];
    }

    public function test_deposit_is_credited_once_even_after_webhook_retry(): void
    {
        $service = app(CommerceService::class);
        $payment = $service->create($this->customer(), 'deposit', ['prisoner_id' => $this->prisoner()->id, 'amount' => 50000], 'deposit-1');
        $payload = $this->payload($payment);
        $this->postJson('/api/webhooks/sepay', $payload)->assertUnauthorized();
        $this->withHeader('Authorization', 'Apikey secret')->postJson('/api/webhooks/sepay', $payload)->assertCreated()->assertJson(['success' => true]);
        $this->postJson('/api/webhooks/sepay', $payload)->assertCreated();
        $this->assertDatabaseCount('prisoner_ledger_entries', 1);
        $this->assertDatabaseCount('bank_transactions', 1);
        $this->assertDatabaseHas('deposit_requests', ['id' => $payment->deposit_request_id, 'status' => 'CREDITED']);
        $this->postJson('/api/webhooks/sepay', $this->payload($payment, 2))->assertCreated();
        $this->assertDatabaseCount('prisoner_ledger_entries', 1);
        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '2', 'reason' => 'PAYMENT_ALREADY_PROCESSED']);
    }

    public function test_mismatched_expired_and_wrong_account_payments_do_not_credit(): void
    {
        $service = app(CommerceService::class);
        $customer = $this->customer();
        $prisoner = $this->prisoner();
        foreach (['AMOUNT_MISMATCH', 'PAYMENT_EXPIRED', 'WRONG_ACCOUNT', 'NOT_INCOMING', 'UNKNOWN_PAYMENT'] as $i => $reason) {
            $payment = $service->create($customer, 'deposit', ['prisoner_id' => $prisoner->id, 'amount' => 50000], 'key-'.$i);
            $payload = $this->payload($payment, $i + 1);
            if ($reason === 'AMOUNT_MISMATCH') {
                $payload['transferAmount'] = 49000;
            }
            if ($reason === 'PAYMENT_EXPIRED') {
                $payment->update(['expires_at' => now()->subMinute()]);
            }
            if ($reason === 'WRONG_ACCOUNT') {
                $payload['accountNumber'] = 'wrong';
            }
            if ($reason === 'NOT_INCOMING') {
                $payload['transferType'] = 'out';
            }
            if ($reason === 'UNKNOWN_PAYMENT') {
                $payload['code'] = 'UNKNOWN';
            }
            $service->receive($payload);
            $this->assertDatabaseHas('bank_transactions', ['sepay_id' => (string) ($i + 1), 'status' => 'REVIEW', 'reason' => $reason]);
        }
        $this->assertDatabaseCount('prisoner_ledger_entries', 0);
    }

    public function test_order_uses_server_prices_and_creation_is_idempotent(): void
    {
        $customer = $this->customer();
        $prisoner = $this->prisoner();
        $product = Product::create(['name' => 'Sữa', 'price' => 20000]);
        Sanctum::actingAs($customer);
        $data = ['prisoner_id' => $prisoner->id, 'items' => [['product_id' => $product->id, 'quantity' => 3]], 'total' => 1];
        $response = $this->withHeader('Idempotency-Key', 'order-1')->postJson('/api/orders', $data)->assertCreated()->assertJsonPath('data.total', 60000);
        $this->postJson('/api/orders', $data)->assertCreated()->assertJsonPath('data.id', $response->json('data.id'));
        $this->assertDatabaseCount('orders', 1);
        $data['items'][0]['quantity'] = 4;
        $this->postJson('/api/orders', $data)->assertConflict();
        $payment = Payment::findOrFail($response->json('payment.id'));
        app(CommerceService::class)->receive($this->payload($payment));
        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'PAID']);
        $this->assertDatabaseCount('prisoner_ledger_entries', 0);
    }

    public function test_customer_cannot_access_other_payments_or_send_to_unrelated_prisoner(): void
    {
        $customer = $this->customer();
        $prisoner = $this->prisoner();
        $payment = app(CommerceService::class)->create($customer, 'deposit', ['prisoner_id' => $prisoner->id, 'amount' => 50000], 'first');
        $other = Customer::create(['phone' => '0999999999', 'role' => 'CUSTOMER', 'is_active' => true]);
        Sanctum::actingAs($other);
        $this->getJson('/api/payments/'.$payment->id)->assertNotFound();
        $this->getJson('/api/deposit-requests/'.$payment->deposit_request_id)->assertNotFound();
        $this->withHeader('Idempotency-Key', 'other')->postJson('/api/deposit-requests', ['prisoner_id' => $prisoner->id, 'amount' => 50000])->assertForbidden();
    }
}
