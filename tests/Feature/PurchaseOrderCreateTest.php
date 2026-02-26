<?php

namespace Tests\Feature;

use App\Livewire\PurchaseOrders\PurchaseOrderForm;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'purchasing']);

        Warehouse::factory()->create(['is_active' => true]);
    }

    protected function createPurchasingUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('purchasing');

        return $user;
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_cannot_access_purchase_order_create_page(): void
    {
        $response = $this->get(route('purchase-orders.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_role_cannot_access_purchase_order_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchase-orders.create'));

        $response->assertForbidden();
    }

    public function test_purchasing_user_can_access_purchase_order_create_page(): void
    {
        $user = $this->createPurchasingUser();

        $response = $this->actingAs($user)->get(route('purchase-orders.create'));

        $response->assertOk();
        $response->assertSeeLivewire(PurchaseOrderForm::class);
    }

    public function test_admin_can_access_purchase_order_create_page(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get(route('purchase-orders.create'));

        $response->assertOk();
        $response->assertSeeLivewire(PurchaseOrderForm::class);
    }

    public function test_purchase_order_can_be_created_with_valid_data(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('expected_delivery_date', now()->addWeek()->format('Y-m-d'))
            ->set('notes', 'Test notes')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100.50,
                ],
            ])
            ->call('save')
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'created_by' => $user->id,
            'notes' => 'Test notes',
        ]);

        $po = PurchaseOrder::first();
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'qty_ordered' => 10,
            'unit_price' => 100.50,
            'total_price' => 1005.00,
        ]);
    }

    public function test_supplier_is_required(): void
    {
        $user = $this->createPurchasingUser();
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', null)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['supplier_id' => 'required']);
    }

    public function test_supplier_must_exist(): void
    {
        $user = $this->createPurchasingUser();
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', 99999)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['supplier_id' => 'exists']);
    }

    public function test_order_date_is_required(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', '')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['order_date' => 'required']);
    }

    public function test_order_date_must_be_valid_date(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', 'invalid-date')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['order_date' => 'date']);
    }

    public function test_expected_delivery_date_must_be_after_or_equal_to_order_date(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('expected_delivery_date', now()->subDay()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['expected_delivery_date' => 'after_or_equal']);
    }

    public function test_at_least_one_item_is_required(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items' => 'required']);
    }

    public function test_product_id_is_required_for_each_item(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => null,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.product_id' => 'required']);
    }

    public function test_product_must_exist(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => 99999,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.product_id' => 'exists']);
    }

    public function test_quantity_must_be_at_least_one(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 0,
                    'unit_price' => 100,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.qty_ordered' => 'min']);
    }

    public function test_unit_price_cannot_be_negative(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => -1,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.unit_price' => 'min']);
    }

    public function test_totals_are_calculated_correctly(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product1 = Product::factory()->create(['is_active' => true]);
        $product2 = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product1->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
                [
                    'product_id' => $product2->id,
                    'qty_ordered' => 5,
                    'unit_price' => 50,
                ],
            ])
            ->call('save');

        $po = PurchaseOrder::first();
        $expectedSubtotal = (10 * 100) + (5 * 50); // 1250
        $expectedTax = $expectedSubtotal * 0.11; // 137.5
        $expectedTotal = $expectedSubtotal + $expectedTax; // 1387.5

        $this->assertEquals($expectedSubtotal, $po->subtotal);
        $this->assertEquals(round($expectedTax, 2), $po->tax_amount);
        $this->assertEquals(round($expectedTotal, 2), $po->total_amount);
    }

    public function test_po_number_is_generated_automatically(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
            ])
            ->call('save');

        $po = PurchaseOrder::first();
        $this->assertNotNull($po->po_number);
        $this->assertStringStartsWith('PO-' . now()->format('Y') . '-', $po->po_number);
    }

    public function test_can_add_multiple_items(): void
    {
        $user = $this->createPurchasingUser();
        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product1 = Product::factory()->create(['is_active' => true]);
        $product2 = Product::factory()->create(['is_active' => true]);
        $product3 = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product1->id,
                    'qty_ordered' => 10,
                    'unit_price' => 100,
                ],
                [
                    'product_id' => $product2->id,
                    'qty_ordered' => 5,
                    'unit_price' => 50,
                ],
                [
                    'product_id' => $product3->id,
                    'qty_ordered' => 3,
                    'unit_price' => 30,
                ],
            ])
            ->call('save');

        $po = PurchaseOrder::first();
        $this->assertCount(3, $po->items);
    }

    public function test_default_order_date_is_today(): void
    {
        $user = $this->createPurchasingUser();

        $component = Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class);

        $this->assertEquals(now()->format('Y-m-d'), $component->get('order_date'));
    }

    public function test_owner_can_create_purchase_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $supplier = Supplier::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(PurchaseOrderForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('order_date', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'qty_ordered' => 5,
                    'unit_price' => 50,
                ],
            ])
            ->call('save')
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'status' => 'draft',
        ]);
    }
}
