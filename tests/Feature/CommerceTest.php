<?php

namespace Tests\Feature;

use App\Http\Middleware\LogAccess;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Prisoner;
use App\Models\Product;
use App\Models\ProductCategory;
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
            $t->string('name')->nullable();
            $t->string('role')->default('CUSTOMER');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('prisoners', function (Blueprint $t) {
            $t->id();
            $t->boolean('published')->default(true);
            $t->string('username')->nullable();
            $t->string('prisoner_code')->nullable();
            $t->json('phones');
            $t->integer('position')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('prisoner_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prisoner_id');
            $t->string('locale');
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        (require database_path('migrations/2026_11_13_000000_create_commerce_tables.php'))->up();
        (require database_path('migrations/2026_11_19_000000_add_commerce_form_fields.php'))->up();
        (require database_path('migrations/2026_11_19_000100_create_product_categories.php'))->up();
        config(['commerce.bank' => 'MB', 'commerce.account_number' => '12345', 'commerce.account_name' => 'TEST', 'commerce.webhook_key' => 'secret']);
        $this->withoutMiddleware(LogAccess::class);
    }

    private function customer(): Customer
    {
        return Customer::create(['phone' => '0901234567', 'name' => 'Nguyễn Văn A', 'role' => 'NEW', 'is_active' => true]);
    }

    private function prisoner(): Prisoner
    {
        return Prisoner::withoutEvents(fn () => Prisoner::create(['published' => true, 'username' => 'Trần Văn B', 'prisoner_code' => 'PN001', 'phones' => [['phone' => '0901234567']]]));
    }

    private function payload(Payment $payment, int $id = 1): array
    {
        return ['id' => $id, 'accountNumber' => '12345', 'transferType' => 'in', 'transferAmount' => $payment->amount, 'code' => $payment->code];
    }

    private function people(): array
    {
        return [
            'sender' => ['name' => 'Nguyễn Văn A', 'identity_number' => '012345678901', 'phone' => '0901234567', 'birth_year' => 1985, 'address' => 'Hà Nội'],
            'recipient' => ['name' => 'Trần Văn B', 'prisoner_code' => 'PN001', 'birth_year' => 1992, 'gender' => 'MALE', 'address' => 'Hà Nội', 'group' => 'UNKNOWN', 'subcamp' => 'Phân trại 1'],
        ];
    }

    public function test_deposit_is_credited_once_even_after_webhook_retry(): void
    {
        $service = app(CommerceService::class);
        $this->prisoner();
        $payment = $service->create($this->customer(), 'deposit', $this->people() + ['amount' => 50000], 'deposit-1');
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
            $payment = $service->create($customer, 'deposit', $this->people() + ['amount' => 50000], 'key-'.$i);
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
        $category = ProductCategory::create(['name' => 'Đồ uống']);
        $product = Product::create(['name' => 'Sữa', 'price' => 20000, 'category_id' => $category->id]);
        Sanctum::actingAs($customer);
        $data = $this->people() + ['items' => [['product_id' => $product->id, 'quantity' => 3]], 'total' => 1];
        $response = $this->withHeader('Idempotency-Key', 'order-1')->postJson('/api/orders', $data)->assertCreated()->assertJsonPath('data.total', 60000);
        $response->assertJsonPath('data.customer.name', 'Nguyễn Văn A')
            ->assertJsonPath('data.prisoner_id', $prisoner->id)
            ->assertJsonPath('data.sender_details.identity_number', '012345678901')
            ->assertJsonPath('data.recipient_details.subcamp', 'Phân trại 1')
            ->assertJsonPath('data.customer.phone', '0901234567')
            ->assertJsonPath('data.prisoner.username', 'Trần Văn B')
            ->assertJsonPath('data.prisoner.prisoner_code', 'PN001');
        $this->getJson('/api/orders')->assertOk()->assertJsonPath('data.0.customer.name', 'Nguyễn Văn A');
        $this->getJson('/api/orders/'.$response->json('data.id'))->assertOk()->assertJsonPath('data.prisoner.prisoner_code', 'PN001');
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
        $payment = app(CommerceService::class)->create($customer, 'deposit', $this->people() + ['amount' => 50000], 'first');
        $other = Customer::create(['phone' => '0999999999', 'role' => 'NEW', 'is_active' => true]);
        Sanctum::actingAs($other);
        $this->getJson('/api/payments/'.$payment->id)->assertNotFound();
        $this->getJson('/api/deposit-requests/'.$payment->deposit_request_id)->assertNotFound();
        $data = $this->people() + ['amount' => 50000];
        $data['sender']['phone'] = $other->phone;
        $this->withHeader('Idempotency-Key', 'other')->postJson('/api/deposit-requests', $data)->assertForbidden();
    }

    public function test_deposit_stores_form_and_rejects_wrong_prisoner_code(): void
    {
        $customer = $this->customer();
        $prisoner = $this->prisoner();
        Sanctum::actingAs($customer);
        $data = $this->people() + ['amount' => 50000];

        $this->withHeader('Idempotency-Key', 'deposit-form')->postJson('/api/deposit-requests', $data)
            ->assertCreated()
            ->assertJsonPath('data.sender_details.identity_number', '012345678901')
            ->assertJsonPath('data.recipient_details.prisoner_code', 'PN001');

        $data['recipient']['prisoner_code'] = 'WRONG';
        $this->withHeader('Idempotency-Key', 'deposit-wrong')->postJson('/api/deposit-requests', $data)
            ->assertUnprocessable()->assertJsonValidationErrors('recipient.prisoner_code');
        $data['recipient']['prisoner_code'] = 'PN001';
        $data['recipient']['name'] = 'Trần Văn C';
        $this->withHeader('Idempotency-Key', 'deposit-wrong-name')->postJson('/api/deposit-requests', $data)
            ->assertUnprocessable()->assertJsonValidationErrors('recipient.name');
        $this->assertDatabaseCount('deposit_requests', 1);
    }

    public function test_order_rejects_recipient_name_that_does_not_match_prisoner(): void
    {
        Sanctum::actingAs($this->customer());
        $prisoner = $this->prisoner();
        $category = ProductCategory::create(['name' => 'Đồ uống']);
        $product = Product::create(['name' => 'Nước suối', 'price' => 5000, 'category_id' => $category->id]);
        $data = $this->people() + [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ];
        $data['recipient']['name'] = 'Nguyễn Văn C';

        $this->withHeader('Idempotency-Key', 'wrong-recipient-name')->postJson('/api/orders', $data)
            ->assertUnprocessable()->assertJsonValidationErrors('recipient.name');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_order_requires_sender_and_recipient_form(): void
    {
        Sanctum::actingAs($this->customer());
        $this->withHeader('Idempotency-Key', 'missing-form')->postJson('/api/orders', [
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['sender', 'recipient']);
    }

    public function test_products_can_be_filtered_for_canteen(): void
    {
        Sanctum::actingAs($this->customer());
        $drinks = ProductCategory::create(['name' => 'Đồ uống', 'position' => 1]);
        $food = ProductCategory::create(['name' => 'Đồ ăn', 'position' => 2]);
        Product::create(['name' => 'Nước suối Aqua', 'price' => 5000, 'category_id' => $drinks->id, 'image_url' => 'https://example.com/aqua.jpg']);
        Product::create(['name' => 'Bánh quy', 'price' => 10000, 'category_id' => $food->id]);

        $this->getJson('/api/product-categories')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Đồ uống');
        $this->getJson('/api/products?category_id='.$drinks->id.'&search=Aqua')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $drinks->id)
            ->assertJsonPath('data.0.category', 'Đồ uống')
            ->assertJsonPath('data.0.image_url', 'https://example.com/aqua.jpg');

        $drinks->update(['is_active' => false]);
        $this->getJson('/api/product-categories')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_only_new_role_can_use_commerce_apis(): void
    {
        foreach (['CUSTOMER', 'GATE'] as $role) {
            $customer = Customer::create(['phone' => $role === 'CUSTOMER' ? '0901111111' : '0902222222', 'role' => $role, 'is_active' => true]);
            Sanctum::actingAs($customer);

            foreach (['/api/product-categories', '/api/products', '/api/orders', '/api/orders/1', '/api/deposit-requests', '/api/deposit-requests/1', '/api/payments/1'] as $path) {
                $this->getJson($path)->assertForbidden();
            }

            $this->postJson('/api/orders', [])->assertForbidden();
            $this->postJson('/api/deposit-requests', [])->assertForbidden();
        }

        Sanctum::actingAs($this->customer());
        $this->getJson('/api/user/me')->assertOk()->assertJsonPath('user.role', 'NEW');
        $this->getJson('/api/products')->assertOk();
        $this->getJson('/api/deposit-requests')->assertOk();
        $this->getJson('/api/surveys')->assertForbidden();
    }
}
