<?php

namespace Tests\Feature;

use App\Livewire\Products\ProductForm;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);

        // Create warehouse for stock initialization
        Warehouse::factory()->create(['is_active' => true]);
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_cannot_access_product_create_page(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_role_cannot_access_product_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.create'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_product_create_page(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get(route('products.create'));

        $response->assertOk();
        $response->assertSeeLivewire(ProductForm::class);
    }

    public function test_product_can_be_created_with_valid_data(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('description', 'This is a test product')
            ->set('unit', 'pcs')
            ->set('category', 'Electronics')
            ->set('min_stock', 10)
            ->set('safety_stock', 5)
            ->set('target_stock', 100)
            ->set('lead_time_days', 7)
            ->set('track_batch', true)
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'unit' => 'pcs',
            'category' => 'Electronics',
            'min_stock' => 10,
            'safety_stock' => 5,
            'target_stock' => 100,
            'lead_time_days' => 7,
            'track_batch' => true,
            'is_active' => true,
        ]);
    }

    public function test_sku_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', '')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->call('save')
            ->assertHasErrors(['sku' => 'required']);
    }

    public function test_sku_must_be_unique(): void
    {
        $user = $this->createAdmin();
        Product::create([
            'sku' => 'EXISTING-001',
            'name' => 'Existing Product',
            'unit' => 'pcs',
            'min_stock' => 0,
            'safety_stock' => 0,
            'lead_time_days' => 7,
        ]);

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'EXISTING-001')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);
    }

    public function test_name_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', '')
            ->set('unit', 'pcs')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_unit_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('unit', '')
            ->call('save')
            ->assertHasErrors(['unit' => 'required']);
    }

    public function test_min_stock_must_be_non_negative(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->set('min_stock', -1)
            ->call('save')
            ->assertHasErrors(['min_stock' => 'min']);
    }

    public function test_safety_stock_must_be_non_negative(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->set('safety_stock', -1)
            ->call('save')
            ->assertHasErrors(['safety_stock' => 'min']);
    }

    public function test_lead_time_must_be_at_least_one_day(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->set('lead_time_days', 0)
            ->call('save')
            ->assertHasErrors(['lead_time_days' => 'min']);
    }

    public function test_stock_records_are_created_for_all_warehouses(): void
    {
        $user = $this->createAdmin();
        Warehouse::factory()->count(3)->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Test Product')
            ->set('unit', 'pcs')
            ->set('min_stock', 0)
            ->set('safety_stock', 0)
            ->set('lead_time_days', 7)
            ->call('save');

        $product = Product::where('sku', 'TEST-001')->first();
        $this->assertCount(4, $product->stocks); // 1 from setUp + 3 new
    }

    public function test_product_can_be_created_with_minimal_data(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'MIN-001')
            ->set('name', 'Minimal Product')
            ->set('unit', 'pcs')
            ->set('min_stock', 0)
            ->set('safety_stock', 0)
            ->set('lead_time_days', 1)
            ->call('save')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'MIN-001',
            'name' => 'Minimal Product',
        ]);
    }

    public function test_owner_can_create_product(): void
    {
        Role::create(['name' => 'warehouse']);
        $user = User::factory()->create();
        $user->assignRole('owner');

        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->set('sku', 'OWNER-001')
            ->set('name', 'Owner Created Product')
            ->set('unit', 'pcs')
            ->set('min_stock', 0)
            ->set('safety_stock', 0)
            ->set('lead_time_days', 7)
            ->call('save')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'OWNER-001',
            'name' => 'Owner Created Product',
        ]);
    }
}
