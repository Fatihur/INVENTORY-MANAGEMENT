<?php

namespace Tests\Feature;

use App\Livewire\Suppliers\SupplierForm;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'purchasing']);
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

    public function test_guest_cannot_access_supplier_create_page(): void
    {
        $response = $this->get(route('suppliers.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_role_cannot_access_supplier_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('suppliers.create'));

        $response->assertForbidden();
    }

    public function test_purchasing_user_can_access_supplier_create_page(): void
    {
        $user = $this->createPurchasingUser();

        $response = $this->actingAs($user)->get(route('suppliers.create'));

        $response->assertOk();
        $response->assertSeeLivewire(SupplierForm::class);
    }

    public function test_admin_can_access_supplier_create_page(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get(route('suppliers.create'));

        $response->assertOk();
        $response->assertSeeLivewire(SupplierForm::class);
    }

    public function test_supplier_can_be_created_with_valid_data(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'SUP-001')
            ->set('name', 'Test Supplier')
            ->set('contact_person', 'John Doe')
            ->set('phone', '08123456789')
            ->set('email', 'supplier@test.com')
            ->set('address', 'Jl. Test No. 123')
            ->set('default_lead_time_days', 7)
            ->set('default_payment_terms', 30)
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-001',
            'name' => 'Test Supplier',
            'contact_person' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'supplier@test.com',
            'address' => 'Jl. Test No. 123',
            'default_lead_time_days' => 7,
            'default_payment_terms' => 30,
            'is_active' => true,
        ]);
    }

    public function test_code_is_required(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', '')
            ->set('name', 'Test Supplier')
            ->set('default_lead_time_days', 7)
            ->call('save')
            ->assertHasErrors(['code' => 'required']);
    }

    public function test_code_must_be_unique(): void
    {
        $user = $this->createPurchasingUser();
        Supplier::create([
            'code' => 'EXISTING-001',
            'name' => 'Existing Supplier',
            'default_lead_time_days' => 7,
        ]);

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'EXISTING-001')
            ->set('name', 'Test Supplier')
            ->set('default_lead_time_days', 7)
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    public function test_name_is_required(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'SUP-001')
            ->set('name', '')
            ->set('default_lead_time_days', 7)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_lead_time_must_be_at_least_one_day(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'SUP-001')
            ->set('name', 'Test Supplier')
            ->set('default_lead_time_days', 0)
            ->call('save')
            ->assertHasErrors(['default_lead_time_days' => 'min']);
    }

    public function test_email_must_be_valid(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'SUP-001')
            ->set('name', 'Test Supplier')
            ->set('default_lead_time_days', 7)
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_payment_terms_must_be_non_negative(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'SUP-001')
            ->set('name', 'Test Supplier')
            ->set('default_lead_time_days', 7)
            ->set('default_payment_terms', -1)
            ->call('save')
            ->assertHasErrors(['default_payment_terms' => 'min']);
    }

    public function test_supplier_can_be_created_with_minimal_data(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'MIN-001')
            ->set('name', 'Minimal Supplier')
            ->set('default_lead_time_days', 1)
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'code' => 'MIN-001',
            'name' => 'Minimal Supplier',
        ]);
    }

    public function test_optional_fields_can_be_empty(): void
    {
        $user = $this->createPurchasingUser();

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'OPT-001')
            ->set('name', 'Optional Supplier')
            ->set('contact_person', '')
            ->set('phone', '')
            ->set('email', '')
            ->set('address', '')
            ->set('default_payment_terms', null)
            ->set('default_lead_time_days', 7)
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'code' => 'OPT-001',
            'name' => 'Optional Supplier',
            'contact_person' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
        ]);
    }

    public function test_owner_can_create_supplier(): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        Livewire::actingAs($user)
            ->test(SupplierForm::class)
            ->set('code', 'OWNER-SUP')
            ->set('name', 'Owner Created Supplier')
            ->set('default_lead_time_days', 7)
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'code' => 'OWNER-SUP',
            'name' => 'Owner Created Supplier',
        ]);
    }

    public function test_default_lead_time_is_set_to_7_days(): void
    {
        $user = $this->createPurchasingUser();

        $component = Livewire::actingAs($user)
            ->test(SupplierForm::class);

        // The component has a default value of 7
        $this->assertEquals(7, $component->get('default_lead_time_days'));
    }
}
