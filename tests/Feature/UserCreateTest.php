<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'warehouse']);
        Role::create(['name' => 'purchasing']);
        Role::create(['name' => 'manager']);
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function createOwner(): User
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        return $user;
    }

    public function test_guest_cannot_access_user_create_page(): void
    {
        $response = $this->get(route('users.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_role_cannot_access_user_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_user_create_page(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertOk();
        $response->assertSeeLivewire(UserForm::class);
    }

    public function test_owner_can_access_user_create_page(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertOk();
        $response->assertSeeLivewire(UserForm::class);
    }

    public function test_user_can_be_created_with_valid_data(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('warehouse'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_name_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', '')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_email_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', '')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_email_must_be_valid(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_email_must_be_unique(): void
    {
        $user = $this->createAdmin();
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_password_is_required_on_create(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['password' => 'required']);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['password' => 'min']);
    }

    public function test_password_must_be_confirmed(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different123')
            ->set('role', 'warehouse')
            ->call('save')
            ->assertHasErrors(['password' => 'confirmed']);
    }

    public function test_role_is_required(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', '')
            ->call('save')
            ->assertHasErrors(['role' => 'required']);
    }

    public function test_role_must_exist(): void
    {
        $user = $this->createAdmin();

        Livewire::actingAs($user)
            ->test(UserForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'nonexistent-role')
            ->call('save')
            ->assertHasErrors(['role' => 'exists']);
    }

    public function test_user_can_be_created_with_different_roles(): void
    {
        $admin = $this->createAdmin();
        $roles = ['owner', 'admin', 'warehouse', 'purchasing', 'manager'];

        foreach ($roles as $index => $role) {
            Livewire::actingAs($admin)
                ->test(UserForm::class)
                ->set('name', "Test User {$role}")
                ->set('email', "test-{$index}@example.com")
                ->set('password', 'password123')
                ->set('password_confirmation', 'password123')
                ->set('role', $role)
                ->call('save');

            $user = User::where("email", "test-{$index}@example.com")->first();
            $this->assertTrue($user->hasRole($role));
        }
    }

    public function test_default_role_is_warehouse(): void
    {
        $user = $this->createAdmin();

        $component = Livewire::actingAs($user)
            ->test(UserForm::class);

        $this->assertEquals('warehouse', $component->get('role'));
    }

    public function test_owner_can_create_user(): void
    {
        $owner = $this->createOwner();

        Livewire::actingAs($owner)
            ->test(UserForm::class)
            ->set('name', 'Owner Created User')
            ->set('email', 'owner-created@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'admin')
            ->call('save')
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Owner Created User',
            'email' => 'owner-created@example.com',
        ]);

        $user = User::where('email', 'owner-created@example.com')->first();
        $this->assertTrue($user->hasRole('admin'));
    }
}
