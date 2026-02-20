<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $roleFilter = null;

    public function delete(int $userId)
    {
        $user = User::findOrFail($userId);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account');
            return;
        }

        // Prevent deleting owner
        if ($user->hasRole('owner')) {
            session()->flash('error', 'Cannot delete owner account');
            return;
        }

        $user->delete();
        session()->flash('message', 'User deleted successfully');
    }

    public function render()
    {
        $query = User::with('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($q) {
                $q->whereHas('roles', function ($sq) {
                    $sq->where('name', $this->roleFilter);
                });
            });

        return view('livewire.users.user-list', [
            'users' => $query->paginate(15),
            'roles' => ['owner', 'admin', 'warehouse', 'purchasing', 'manager'],
        ]);
    }
}
