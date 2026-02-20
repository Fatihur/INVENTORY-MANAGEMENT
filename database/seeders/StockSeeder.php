<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Models\Batch;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::all();

        if ($warehouses->isEmpty() || $products->isEmpty()) {
            return;
        }

        $mainWh = $warehouses->first();

        // 1. Create Bins for Main Warehouse
        for ($i = 1; $i <= 10; $i++) {
            BinLocation::firstOrCreate([
                'code' => 'BIN' . str_pad($i, 5, '0', STR_PAD_LEFT),
            ], [
                'warehouse_id' => $mainWh->id,
                'zone' => 'ZONE-A',
                'aisle' => 'A' . str_pad(intdiv($i - 1, 5) + 1, 2, '0', STR_PAD_LEFT),
                'rack' => 'R' . str_pad(($i - 1) % 5 + 1, 2, '0', STR_PAD_LEFT),
                'shelf' => 'S1',
                'bin' => 'B' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'capacity' => 1000,
                'current_qty' => 0,
                'is_active' => true,
            ]);
        }

        $bins = BinLocation::where('warehouse_id', $mainWh->id)->get();

        // 2. Insert Stocks per product
        foreach ($products as $index => $product) {
            $bin = $bins->random();
            $qty = rand(50, 300);

            // Handle Batches if applicable
            $batchId = null;
            if ($product->track_batch) {
                $batch = Batch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'BATCH-' . strtoupper(Str::random(6)),
                    'manufacturing_date' => now()->subMonths(rand(1,6)),
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                    'initial_qty' => $qty,
                    'remaining_qty' => $qty,
                    'warehouse_id' => $mainWh->id,
                    'cost_price' => $product->cost_price ?? 0,
                    'is_active' => true,
                ]);
                $batchId = $batch->id;
            }

            // Create Stock Record
            $stock = Stock::firstOrCreate([
                'product_id' => $product->id,
                'warehouse_id' => $mainWh->id,
                'bin_location_id' => $bin->id,
                'batch_id' => $batchId,
            ], [
                'qty_on_hand' => $qty,
                'qty_allocated' => 0,
                'qty_available' => $qty,
            ]);

            // Update Bin Capacity
            $bin->increment('current_qty', $qty);

            // Create Movement Log
            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => 'in',
                'quantity' => $qty,
                'balance_after' => $qty,
                'reference_type' => 'INITIAL_SYSTEM_SEED',
                'reference_id' => 0,
                'notes' => 'Seeding initial inventory data',
                'created_by' => 1,
            ]);
        }
    }
}
