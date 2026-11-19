<?php

namespace Tests\Feature;

use A17\Twill\Facades\TwillPermissions;
use A17\Twill\Models\User as AdminUser;
use A17\Twill\Models\Role as TwillRole;
use A17\Twill\Facades\TwillNavigation;
use App\Http\Middleware\LogAccess;
use App\Models\Enums\AdminRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminCommerceRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    public function test_new_role_is_available_for_twill_users(): void
    {
        $this->assertSame(AdminRole::class, TwillPermissions::roles());
        $this->assertSame('NEW', AdminRole::toArray()['NEW']);
    }

    public function test_new_role_is_redirected_to_commerce_and_cannot_access_other_admin_sections(): void
    {
        $this->withoutMiddleware(LogAccess::class);

        $user = new AdminUser(['role' => 'NEW', 'published' => true]);
        $user->id = 1;

        $this->actingAs($user, 'twill_users');

        $this->assertTrue(Gate::forUser($user)->allows('access-commerce'));
        $this->assertTrue(Gate::forUser($user)->denies('access-admin-operations'));
        $this->assertTrue(Gate::forUser($user)->denies('list'));
        $this->get('/admin')->assertRedirect(route('admin.commerce.index'));
        $this->getJson('/admin/customers')->assertForbidden();
        $this->getJson('/admin/access-logs')->assertForbidden();

        $this->artisan('migrate', ['--force' => true]);
        $this->get('/admin/commerce')->assertOk();
        $this->get('/admin/commerce?section=deposits')->assertOk();
        $this->get('/admin/commerce?section=categories')->assertOk();
        $this->post('/admin/commerce/categories', ['name' => 'Đồ uống', 'position' => 1, 'is_active' => 1])->assertRedirect();
        $this->assertDatabaseHas('product_categories', ['name' => 'Đồ uống']);
        $categoryId = \App\Models\ProductCategory::where('name', 'Đồ uống')->value('id');
        $this->get('/admin/commerce?section=products')->assertOk()->assertSee('Đồ uống');
        $this->post('/admin/commerce/products', [
            'name' => 'Nước suối', 'category_id' => $categoryId, 'price' => 5000, 'is_active' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Nước suối', 'category_id' => $categoryId]);
    }

    public function test_new_database_role_only_sees_commerce_when_twill_permissions_are_enabled(): void
    {
        config(['twill.enabled.permissions-management' => true]);
        $this->withoutMiddleware(LogAccess::class);
        $this->artisan('migrate', ['--force' => true]);

        $user = new AdminUser(['published' => true]);
        $user->id = 1;
        $user->setRelation('role', new TwillRole(['name' => 'NEW', 'published' => true]));
        $this->actingAs($user, 'twill_users');

        $this->assertTrue(AdminRole::isNewUser($user));
        $this->assertTrue(Gate::forUser($user)->allows('access-commerce'));
        $this->assertTrue(Gate::forUser($user)->denies('access-admin-operations'));
        $this->assertTrue(Gate::forUser($user)->denies('manage-modules'));
        $this->get('/admin')->assertRedirect(route('admin.commerce.index'));
        $this->get('/admin/commerce')->assertOk();
        $this->getJson('/admin/customers')->assertForbidden();

        $links = TwillNavigation::buildNavigationTree()['left'];
        $this->assertCount(1, $links);
        $this->assertSame('Đơn hàng và lưu ký', reset($links)->getTitle());

        $user->is_superadmin = true;
        $this->assertTrue(AdminRole::isNewUser($user));
        $this->assertTrue(Gate::forUser($user)->denies('manage-modules'));
        $this->assertTrue(Gate::forUser($user)->denies('access-admin-operations'));
        $this->getJson('/admin/customers')->assertForbidden();
    }

    public function test_new_role_can_upload_and_replace_product_image(): void
    {
        $this->withoutMiddleware(LogAccess::class);
        $this->artisan('migrate', ['--force' => true]);
        Storage::fake('public');
        $user = new AdminUser(['role' => 'NEW', 'published' => true]);
        $user->id = 1;
        $this->actingAs($user, 'twill_users');
        $category = \App\Models\ProductCategory::create(['name' => 'Đồ uống']);
        $imageBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL/nwAAAABJRU5ErkJggg=='
        );
        $image = fn () => UploadedFile::fake()->createWithContent('product.png', $imageBytes);

        $form = ['name' => 'Nước suối', 'category_id' => $category->id, 'price' => 5000, 'is_active' => 1];
        $this->post('/admin/commerce/products', $form + ['image' => $image()])->assertRedirect()->assertSessionHasNoErrors();
        $product = \App\Models\Product::where('name', 'Nước suối')->firstOrFail();
        $firstPath = $product->image_path;
        $firstUrl = $product->image_url;
        Storage::disk('public')->assertExists($firstPath);
        $this->assertStringContainsString('/product-images/'.$product->id, $product->image_url);
        $this->assertSame($imageBytes, $this->get(parse_url($product->image_url, PHP_URL_PATH))->streamedContent());

        $this->post('/admin/commerce/products', $form + ['id' => $product->id, 'image' => $image()])
            ->assertRedirect()->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertNotSame($firstUrl, $product->image_url);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($product->image_path);
        Storage::disk('public')->delete($product->image_path);
        $this->get(parse_url($product->image_url, PHP_URL_PATH))->assertNotFound();
    }

    public function test_other_admin_roles_cannot_access_commerce(): void
    {
        $this->withoutMiddleware(LogAccess::class);

        foreach (['ADMIN', 'PUBLISHER', 'VIEWONLY', 'SUPERADMIN'] as $role) {
            $user = new AdminUser(['role' => $role, 'published' => true]);
            $user->id = 1;
            $this->actingAs($user, 'twill_users');

            $this->assertTrue(Gate::forUser($user)->denies('access-commerce'));
            $this->getJson('/admin/commerce')->assertForbidden();
            $this->getJson('/admin/commerce?section=deposits')->assertForbidden();
            $this->postJson('/admin/commerce/products', [])->assertForbidden();
            $this->postJson('/admin/commerce/categories', [])->assertForbidden();
            $this->postJson('/admin/commerce/orders/1', [])->assertForbidden();
        }
    }
}
