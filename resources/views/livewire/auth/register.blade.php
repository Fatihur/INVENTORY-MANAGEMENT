<div class="login-box">
    <div class="login-header">
        <h1>SMART INVENTORY</h1>
        <small>Create new account</small>
    </div>

    <div class="login-body">
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form wire:submit="register">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" wire:model="name" class="form-control" placeholder="Enter name">
                @error('name') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" wire:model="email" class="form-control" placeholder="Enter email">
                @error('email') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" wire:model="password" class="form-control" placeholder="Enter password">
                @error('password') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
            <label class="form-label">Confirm Password</label>
                <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Confirm password">
            </div>

            <button type="submit" class="btn btn-primary">REGISTER</button>
        </form>

        <div style="text-align: center; margin-top: 15px; font-size: 11px;">
            <a wire:navigate href="{{ route('login') }}" style="color: #3498db;">Already have an account? Login</a>
        </div>
    </div>
</div>
