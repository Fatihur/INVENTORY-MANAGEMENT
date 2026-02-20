<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">{{ $user ? 'Edit User' : 'Add User' }}</h2>

    <form wire:submit="save">
        <div class="panel">
            <div class="panel-header">User Information</div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Name <span style="color: #e74c3c;">*</span></label>
                        <input type="text" wire:model="name" class="form-control">
                        @error('name')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color: #e74c3c;">*</span></label>
                        <input type="email" wire:model="email" class="form-control">
                        @error('email')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role <span style="color: #e74c3c;">*</span></label>
                        <select wire:model="role" class="form-control">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer; margin-top: 25px;">
                            <input type="checkbox" wire:model="is_active" style="margin-right: 5px;">
                            Active
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ $user ? 'New Password (leave blank to keep current)' : 'Password *' }}</label>
                        <input type="password" wire:model="password" class="form-control">
                        @error('password')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ $user ? 'Confirm New Password' : 'Confirm Password *' }}</label>
                        <input type="password" wire:model="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">{{ $user ? 'Update' : 'Create' }} User</button>
            <a wire:navigate href="{{ route('users.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
