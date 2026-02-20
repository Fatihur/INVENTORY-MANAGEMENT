<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $user = User::first();

        if ($suppliers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['draft', 'pending_approval', 'approved', 'processing', 'completed'];

        for ($i = 1; $i <= 5; $i++) {
            $supplier = $suppliers->random();
            $status = $statuses[array_rand($statuses)];
            
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'status' => $status,
                'order_date' => now()->subDays(rand(1, 45)),
                'expected_date' => now()->addDays(rand(1, 14)),
                'notes' => 'Seeded PO for ' . $supplier->name,
                'created_by' => $user->id,
            ]);

            $orderProducts = $products->random(rand(2, 5));
            foreach ($orderProducts as $prod) {
                $qty = rand(10, 100);
                $price = $prod->cost_price ?? rand(5000, 50000);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $prod->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => 11,
                    'discount_percent' => 0,
                    'subtotal' => $qty * $price,
                    'total' => ($qty * $price) * 1.11,
                ]);
            }
            $po->calculateTotals();
        }
    }
}
