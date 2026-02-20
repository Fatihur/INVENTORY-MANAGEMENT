<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Users</h2>
        @can('users.create')
            <a wire:navigate href="{{ route('users.create') }}" class="btn btn-primary">+ Add User</a>
        @endcan
    </div>

    @if(session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif

    <div class="panel-body" style="background-color: #fff; border: 1px solid #bdc3c7; padding: 10px; margin-bottom: 15px;">
        <div style="display: flex; gap: 10px;">
            <input type="text" wire:model.live="search" placeholder="Search users..." class="form-control" style="width: 250px;">
            <select wire:model.live="roleFilter" class="form-control" style="width: 150px;">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-info">{{ $user->roles->first()->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a wire:navigate href="{{ route('users.edit', $user) }}" style="color: #3498db;">Edit</a>
                            @if($user->id !== auth()->id())
                                <button wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" style="color: #e74c3c; margin-left: 10px; background: none; border: none; cursor: pointer;" onclick="return confirm('Are you sure?')">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #7f8c8d;">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">{{ $users->links() }}</div>
    </div>
</div>
