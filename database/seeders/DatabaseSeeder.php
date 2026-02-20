<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            WarehouseSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            StockSeeder::class,
            ApprovalSeeder::class,
            PurchaseOrderSeeder::class,
            SalesOrderSeeder::class,
            StockOpnameSeeder::class,
        ]);
    }
}
