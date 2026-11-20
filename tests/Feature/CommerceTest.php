<?php

namespace Tests\Feature;

use A17\Twill\Models\User as AdminUser;
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
        (require database_path('migrations/2026_11_20_000000_add_bank_transaction_reconciliation.php'))->up();
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

    public function test_webhook_matches_one_order_code_even_when_sepay_code_differs_or_memo_repeats(): void
    {
        $this->prisoner();
        $customer = $this->customer();
        $category = ProductCategory::create(['name' => 'Đồ uống']);
        $product = Product::create(['name' => 'Sữa', 'price' => 16000, 'category_id' => $category->id]);
        $payment = app(CommerceService::class)->create($customer, 'order', $this->people() + [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], 'matched-order');
        $payload = $this->payload($payment);
        $payload['code'] = 'DH261120K7ZBQ9';
        $payload['content'] = 'SEVQR '.$payment->code.' '.$payment->code;

        app(CommerceService::class)->receive($payload);

        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'PAID']);
        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '1', 'status' => 'MATCHED', 'payment_id' => $payment->id]);
    }

    public function test_webhook_matches_short_order_code_in_vietinbank_memo(): void
    {
        $this->prisoner();
        $category = ProductCategory::create(['name' => 'Food']);
        $product = Product::create(['name' => 'Item', 'price' => 16000, 'category_id' => $category->id]);
        $payment = app(CommerceService::class)->create($this->customer(), 'order', $this->people() + [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], 'DH261120T9RHXE');
        $payment->update(['bank' => 'VietinBank', 'account_number' => '101883618963']);
        $this->assertNotSame('DH261120T9RHXE', $payment->code);

        $this->withHeader('Authorization', 'Apikey secret')->postJson('/api/webhooks/sepay', [
            'id' => 101005705,
            'gateway' => 'VietinBank',
            'transactionDate' => '2026-11-20 09:28:05',
            'accountNumber' => '101883618963',
            'subAccount' => null,
            'code' => 'DH261120',
            'content' => '164D61120Q9JNNRA SEVQR DH261120T9RHXE',
            'transferType' => 'in',
            'transferAmount' => 16000,
            'referenceCode' => '164D61120Q9JNNRA',
        ])->assertCreated();

        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'PAID']);
        $this->assertDatabaseHas('bank_transactions', [
            'sepay_id' => '101005705', 'status' => 'MATCHED', 'payment_id' => $payment->id,
        ]);
    }

    public function test_webhook_does_not_choose_between_two_known_payment_codes(): void
    {
        $this->prisoner();
        $customer = $this->customer();
        $service = app(CommerceService::class);
        $first = $service->create($customer, 'deposit', $this->people() + ['amount' => 50000], 'first-code');
        $second = $service->create($customer, 'deposit', $this->people() + ['amount' => 50000], 'second-code');
        $payload = $this->payload($first);
        $payload['code'] = null;
        $payload['content'] = $first->code.' '.$second->code;

        $service->receive($payload);

        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '1', 'status' => 'REVIEW', 'reason' => 'AMBIGUOUS_PAYMENT']);
        $this->assertDatabaseHas('payments', ['id' => $first->id, 'status' => 'PENDING']);
        $this->assertDatabaseHas('payments', ['id' => $second->id, 'status' => 'PENDING']);
    }

    public function test_unknown_transfer_requires_review_and_can_be_reconciled_once_to_matching_order(): void
    {
        $this->prisoner();
        $customer = $this->customer();
        $category = ProductCategory::create(['name' => 'Đồ uống']);
        $product = Product::create(['name' => 'Sữa', 'price' => 16000, 'category_id' => $category->id]);
        $payment = app(CommerceService::class)->create($customer, 'order', $this->people() + [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], 'reviewed-order');
        $payload = $this->payload($payment);
        $payload['code'] = null;
        $payload['content'] = 'SEVQR DH261120K7ZBQ9';
        $service = app(CommerceService::class);
        $service->receive($payload);
        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '1', 'status' => 'REVIEW', 'reason' => 'UNKNOWN_PAYMENT']);
        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'AWAITING_PAYMENT']);

        $admin = new AdminUser(['role' => 'NEW', 'published' => true]);
        $admin->id = 7;
        $this->actingAs($admin, 'twill_users');
        $this->post('/admin/commerce/transactions/1/reconcile', [
            'payment_code' => strtolower($payment->code),
            'note' => 'Đã xác minh sao kê và người chuyển tiền.',
            'confirmed' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'PAID']);
        $this->assertDatabaseHas('bank_transactions', [
            'sepay_id' => '1', 'status' => 'MATCHED', 'reason' => 'MANUALLY_MATCHED',
            'payment_id' => $payment->id, 'reconciled_by' => 7,
        ]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->reconcileOrder(1, $payment->order_id, 7, 'Không được ghi nhận hai lần.');
    }

    public function test_manual_reconciliation_rejects_wrong_amount(): void
    {
        $this->prisoner();
        $customer = $this->customer();
        $category = ProductCategory::create(['name' => 'Đồ uống']);
        $product = Product::create(['name' => 'Sữa', 'price' => 16000, 'category_id' => $category->id]);
        $service = app(CommerceService::class);
        $payment = $service->create($customer, 'order', $this->people() + [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], 'wrong-amount');
        $payload = $this->payload($payment);
        $payload['code'] = null;
        $payload['content'] = 'SEVQR DH261120K7ZBQ9';
        $payload['transferAmount'] = 15000;
        $service->receive($payload);

        try {
            $service->reconcileOrder(1, $payment->order_id, 7, 'Không được chấp nhận do sai số tiền.');
            $this->fail('A mismatched transfer must not be reconciled.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'AWAITING_PAYMENT']);
        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '1', 'status' => 'REVIEW', 'payment_id' => null]);
    }

    public function test_vietinbank_qr_contains_sevqr_and_the_order_code(): void
    {
        $payment = new Payment(['bank' => 'VietinBank', 'account_number' => '12345', 'amount' => 16000, 'code' => 'DH'.str_repeat('A', 20), 'status' => 'PENDING', 'expires_at' => now()->addMinutes(30)]);
        $data = app(CommerceService::class)->paymentData($payment);
        parse_str(parse_url($data['qr_url'], PHP_URL_QUERY), $query);

        $this->assertSame('SEVQR '.$payment->code, $data['transfer_content']);
        $this->assertSame($data['transfer_content'], $query['des']);
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

    public function test_order_key_is_the_payment_code_used_by_vietinbank_webhook(): void
    {
        config(['commerce.bank' => 'VietinBank', 'commerce.account_number' => '101883618963']);
        $this->prisoner();
        $category = ProductCategory::create(['name' => 'Food']);
        $product = Product::create(['name' => 'Item', 'price' => 5000, 'category_id' => $category->id]);
        Sanctum::actingAs($this->customer());

        $response = $this->withHeader('Idempotency-Key', 'order-with-transfer-code')
            ->postJson('/api/orders', $this->people() + [
                'key' => 'DH261120UWAVV2',
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])->assertCreated()
            ->assertJsonPath('payment.code', 'DH261120UWAVV2')
            ->assertJsonPath('payment.transfer_content', 'SEVQR DH261120UWAVV2');

        $this->withHeader('Authorization', 'Apikey secret')->postJson('/api/webhooks/sepay', [
            'id' => 101112949,
            'gateway' => 'VietinBank',
            'accountNumber' => '101883618963',
            'subAccount' => null,
            'code' => 'DH261120',
            'content' => '164D61120J15TVS9 SEVQR DH261120UWAVV2',
            'transferType' => 'in',
            'transferAmount' => 5000,
        ])->assertCreated();

        $this->assertDatabaseHas('orders', ['id' => $response->json('data.id'), 'status' => 'PAID']);
        $this->assertDatabaseHas('bank_transactions', ['sepay_id' => '101112949', 'status' => 'MATCHED']);

        $this->withHeader('Idempotency-Key', 'different-order-same-code')
            ->postJson('/api/orders', $this->people() + [
                'key' => 'DH261120UWAVV2',
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])->assertUnprocessable()->assertJsonValidationErrors('key');
        $this->assertDatabaseCount('orders', 1);
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
