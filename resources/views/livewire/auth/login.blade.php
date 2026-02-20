<div class="login-box">
    <div class="login-header">
        <h1>SMART INVENTORY</h1>
        <small>Please login to continue</small>
    </div>

    <div class="login-body">
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form wire:submit="login">
            <div class="form-group">
                <label class="form-label">Username / Email</label>
                <input type="text" wire:model="email" class="form-control" placeholder="Enter email">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" wire:model="password" class="form-control" placeholder="Enter password">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" wire:model="remember"> Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary">LOGIN</button>
        </form>

        <div class="demo-accounts">
            <div class="text-muted text-center"><strong>DEMO ACCOUNTS:</strong></div>
            <div class="text-center" style="margin-top: 8px;">
                <div>owner@inventory.test / password</div>
                <div>admin@inventory.test / password</div>
                <div>warehouse@inventory.test / password</div>
                <div>purchasing@inventory.test / password</div>
                <div>manager@inventory.test / password</div>
            </div>
        </div>
    </div>
</div>
