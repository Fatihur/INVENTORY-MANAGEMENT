<?php

namespace Database\Seeders;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $warehouses = Warehouse::all();
        $user = User::first();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['draft', 'pending', 'confirmed', 'processing', 'shipped', 'completed'];

        for ($i = 1; $i <= 10; $i++) {
            $customer = $customers->random();
            $status = $statuses[array_rand($statuses)];
            
            $so = SalesOrder::create([
                'so_number' => 'SO-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouses->first()?->id,
                'status' => $status,
                'order_date' => now()->subDays(rand(1, 30)),
                'delivery_date' => now()->addDays(rand(1, 7)),
                'notes' => 'Seeded Sales Order',
                'created_by' => $user->id,
            ]);

            $orderProducts = $products->random(rand(1, 4));
            foreach ($orderProducts as $prod) {
                $qty = rand(1, 20);
                $price = $prod->selling_price ?? rand(6000, 60000);
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $prod->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => 11,
                    'discount_percent' => rand(0, 5),
                    'subtotal' => $qty * $price,
                    'total' => ($qty * $price) * 1.11,
                ]);
            }
            $so->calculateTotals();
        }
    }
}
