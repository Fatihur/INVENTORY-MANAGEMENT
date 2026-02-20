<?php

namespace Database\Seeders;

use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockOpnameSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $user = User::first();
        $products = Product::all();

        if ($warehouses->isEmpty() || !$user || $products->isEmpty()) {
            return;
        }

        $mainWh = $warehouses->first();

        $statuses = ['draft', 'in_progress', 'completed'];

        for ($i = 1; $i <= 3; $i++) {
            $status = $statuses[$i - 1]; // Cycle through statuses
            $product = $products->random();
            
            StockOpname::create([
                'warehouse_id' => $mainWh->id,
                'product_id' => $product->id,
                'status' => $status,
                'system_qty' => 100,
                'actual_qty' => 98,
                'completed_at' => $status === 'completed' ? now()->subDays(rand(1, 14))->addHours(2) : null,
                'notes' => 'Seeded Stock Opname routine check',
                'created_by' => $user->id,
            ]);
        }
    }
}
