<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'stock.view', 'stock.scanner', 'stock.in', 'stock.out', 'stock.adjust',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit',
            'purchase-orders.delete', 'purchase-orders.approve', 'purchase-orders.receive',
            'restock.view', 'restock.generate-po',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            'batches.view', 'batches.create', 'batches.edit', 'batches.delete',
            'stock-opname.view', 'stock-opname.create', 'stock-opname.edit', 'stock-opname.delete',
            'bin-locations.view', 'bin-locations.create', 'bin-locations.edit', 'bin-locations.delete',
            'approvals.view', 'approvals.approve', 'approvals.reject',
            'reports.view', 'reports.stock', 'reports.movements',
            'users.view', 'users.create', 'users.edit', 'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (UserRole::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value]);

            match ($roleEnum) {
                UserRole::OWNER => $role->syncPermissions(Permission::all()),
                UserRole::ADMIN => $role->syncPermissions([
                    'products.view', 'products.create', 'products.edit', 'products.delete',
                    'stock.view', 'stock.scanner', 'stock.in', 'stock.out', 'stock.adjust',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                    'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit',
                    'purchase-orders.delete', 'purchase-orders.approve', 'purchase-orders.receive',
                    'restock.view', 'restock.generate-po',
                    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                    'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete',
                    'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
                    'batches.view', 'batches.create', 'batches.edit', 'batches.delete',
                    'stock-opname.view', 'stock-opname.create', 'stock-opname.edit', 'stock-opname.delete',
                    'bin-locations.view', 'bin-locations.create', 'bin-locations.edit', 'bin-locations.delete',
                    'approvals.view', 'approvals.approve', 'approvals.reject',
                    'reports.view', 'reports.stock', 'reports.movements',
                    'users.view', 'users.create', 'users.edit',
                ]),
                UserRole::WAREHOUSE => $role->syncPermissions([
                    'products.view', 'stock.view', 'stock.scanner', 'stock.in', 'stock.out',
                    'purchase-orders.view', 'purchase-orders.receive',
                    'warehouses.view', 'warehouses.edit',
                    'batches.view', 'batches.create', 'batches.edit',
                    'stock-opname.view', 'stock-opname.create', 'stock-opname.edit',
                    'bin-locations.view', 'bin-locations.create', 'bin-locations.edit',
                ]),
                UserRole::PURCHASING => $role->syncPermissions([
                    'products.view', 'stock.view', 'suppliers.view', 'suppliers.create', 'suppliers.edit',
                    'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit', 'purchase-orders.approve',
                    'restock.view', 'restock.generate-po',
                ]),
                UserRole::MANAGER => $role->syncPermissions([
                    'products.view', 'stock.view', 'suppliers.view', 'purchase-orders.view',
                    'customers.view', 'customers.create', 'customers.edit',
                    'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete',
                    'reports.view', 'reports.stock', 'reports.movements',
                    'approvals.view', 'approvals.approve', 'approvals.reject',
                ]),
            };
        }
    }
}
