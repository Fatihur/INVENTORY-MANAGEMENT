<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckInventory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckInventory::class,
            ]);
        }

        // Schedule inventory checks daily
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('inventory:check-alerts')->dailyAt('08:00');
        });

        // Define Gates
        $this->defineGates();
    }

    /**
     * Define application gates.
     */
    protected function defineGates(): void
    {
        // Customers
        Gate::define('customers.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales', 'manager']);
        });

        Gate::define('customers.create', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales']);
        });

        Gate::define('customers.edit', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales']);
        });

        Gate::define('customers.delete', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Sales Orders
        Gate::define('sales-orders.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales', 'warehouse', 'manager']);
        });

        Gate::define('sales-orders.create', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales']);
        });

        Gate::define('sales-orders.edit', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'sales']);
        });

        Gate::define('sales-orders.delete', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Warehouses
        Gate::define('warehouses.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'manager']);
        });

        Gate::define('warehouses.create', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('warehouses.edit', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('warehouses.delete', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Batches
        Gate::define('batches.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'purchasing', 'manager']);
        });

        Gate::define('batches.create', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'purchasing']);
        });

        Gate::define('batches.edit', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse']);
        });

        Gate::define('batches.delete', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Stock Opname
        Gate::define('stock-opname.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'manager']);
        });

        Gate::define('stock-opname.create', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse']);
        });

        Gate::define('stock-opname.approve', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'manager']);
        });

        // Bin Locations
        Gate::define('bin-locations.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'manager']);
        });

        Gate::define('bin-locations.create', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse']);
        });

        Gate::define('bin-locations.edit', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse']);
        });

        Gate::define('bin-locations.delete', function ($user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Approvals
        Gate::define('approvals.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'manager']);
        });

        Gate::define('approvals.process', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'manager']);
        });

        // Reports
        Gate::define('reports.view', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'warehouse', 'purchasing', 'sales', 'manager']);
        });

        Gate::define('reports.export', function ($user) {
            return in_array($user->role, ['owner', 'admin', 'manager']);
        });
    }
}
