<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserForm extends Component
{
    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'warehouse';
    public bool $is_active = true;

    public function mount(?int $user = null)
    {
        if ($user) {
            $this->user = User::findOrFail($user);
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->role = $this->user->roles->first()?->name ?? 'warehouse';
            $this->is_active = $this->user->is_active;
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->user ? ',' . $this->user->id : ''),
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ];

        if (!$this->user) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        if ($this->user) {
            // Update
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
                'is_active' => $this->is_active,
            ]);

            if ($this->password) {
                $this->user->update(['password' => Hash::make($this->password)]);
            }

            // Sync role
            $this->user->syncRoles([$this->role]);

            session()->flash('message', 'User updated successfully');
        } else {
            // Create
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_active' => $this->is_active,
            ]);

            $user->assignRole($this->role);

            session()->flash('message', 'User created successfully');
        }

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.user-form', [
            'roles' => Role::all(),
        ]);
    }
}
