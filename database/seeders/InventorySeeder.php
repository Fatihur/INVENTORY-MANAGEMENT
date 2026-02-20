<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Batch;
use App\Models\SerialNumber;
use App\Models\BinLocation;
use App\Models\StockOpname;
use App\Models\ApprovalLevel;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Create sample customers
        $customersData = [
            [
                'code' => 'CUST00001',
                'name' => 'PT. Maju Jaya',
                'email' => 'contact@majujaya.com',
                'phone' => '081234567890',
                'tax_id' => '01.234.567.8-901.000',
                'credit_limit' => 50000000,
                'payment_terms' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'CUST00002',
                'name' => 'CV. Berkah Sentosa',
                'email' => 'info@berkahsentosa.com',
                'phone' => '081234567891',
                'tax_id' => '01.234.567.8-902.000',
                'credit_limit' => 30000000,
                'payment_terms' => 14,
                'is_active' => true,
            ],
            [
                'code' => 'CUST00003',
                'name' => 'UD. Rejeki Nomplong',
                'email' => 'rejeki@email.com',
                'phone' => '081234567892',
                'tax_id' => null,
                'credit_limit' => 10000000,
                'payment_terms' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($customersData as $customerData) {
            $customer = Customer::firstOrCreate(
                ['code' => $customerData['code']],
                $customerData
            );
            
            if ($customer->wasRecentlyCreated) {
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'type' => 'billing',
                    'address_line1' => 'Jl. Raya Utama No. 123',
                    'address_line2' => 'Kecamatan Sukamaju',
                    'city' => 'Jakarta',
                    'state' => 'DKI Jakarta',
                    'postal_code' => '12345',
                    'country' => 'Indonesia',
                    'is_default' => true,
                ]);
            }
        }

        // Create sample batches
        $products = Product::all();
        $warehouses = Warehouse::all();

        if ($products->count() > 0 && $warehouses->count() > 0) {
            foreach ($products->take(5) as $product) {
                Batch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'BATCH' . str_pad(Batch::count() + 1, 6, '0', STR_PAD_LEFT),
                    'manufacturing_date' => now()->subMonths(2),
                    'expiry_date' => now()->addMonths(10),
                    'initial_qty' => 100,
                    'remaining_qty' => rand(50, 100),
                    'warehouse_id' => $warehouses->first()->id,
                    'cost_price' => $product->cost_price,
                    'is_active' => true,
                ]);
            }
        }

        // Create sample bin locations
        if ($warehouses->count() > 0) {
            $warehouse = $warehouses->first();
            for ($i = 1; $i <= 10; $i++) {
                BinLocation::create([
                    'warehouse_id' => $warehouse->id,
                    'code' => 'BIN' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'zone' => 'ZONE-A',
                    'aisle' => 'A' . str_pad(intdiv($i - 1, 5) + 1, 2, '0', STR_PAD_LEFT),
                    'rack' => 'R' . str_pad(($i - 1) % 5 + 1, 2, '0', STR_PAD_LEFT),
                    'shelf' => 'S1',
                    'bin' => 'B' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'capacity' => 100,
                    'current_qty' => rand(0, 80),
                    'is_active' => true,
                ]);
            }
        }

        // Create approval levels
        $approvalLevels = [
            ['name' => 'Manager Approval', 'model_type' => 'App\\Models\\SalesOrder', 'level_order' => 1, 'role_name' => 'manager'],
            ['name' => 'Director Approval', 'model_type' => 'App\\Models\\SalesOrder', 'level_order' => 2, 'role_name' => 'admin'],
            ['name' => 'Manager Approval', 'model_type' => 'App\\Models\\PurchaseOrder', 'level_order' => 1, 'role_name' => 'manager'],
            ['name' => 'Director Approval', 'model_type' => 'App\\Models\\PurchaseOrder', 'level_order' => 2, 'role_name' => 'admin'],
        ];

        foreach ($approvalLevels as $level) {
            ApprovalLevel::create([
                'name' => $level['name'],
                'model_type' => $level['model_type'],
                'level_order' => $level['level_order'],
                'is_active' => true,
            ]);
        }

        // Create sample sales orders
        $customers = Customer::all();
        if ($customers->count() > 0 && $products->count() > 0) {
            $statuses = ['draft', 'confirmed', 'processing', 'shipped', 'completed'];

            for ($i = 1; $i <= 5; $i++) {
                $customer = $customers->random();
                $status = $statuses[array_rand($statuses)];
                
                $salesOrder = SalesOrder::create([
                    'so_number' => 'SO-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'customer_id' => $customer['code'] === 'CUST00001' ? Customer::where('code', 'CUST00001')->first()->id : Customer::inRandomOrder()->first()->id,
                    'warehouse_id' => $warehouses->count() > 0 ? $warehouses->first()->id : null,
                    'status' => $status,
                    'order_date' => now()->subDays(rand(1, 30)),
                    'delivery_date' => now()->addDays(rand(1, 7)),
                    'notes' => 'Sample order notes',
                    'created_by' => User::first()?->id ?? 1,
                ]);

                // Add order items
                $orderProducts = $products->random(rand(2, 5));
                foreach ($orderProducts as $product) {
                    $qty = rand(1, 10);
                    $price = $product->selling_price;
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate' => 10,
                        'discount_percent' => rand(0, 5),
                        'subtotal' => $qty * $price,
                        'total' => $qty * $price * (1 - rand(0, 5) / 100),
                    ]);
                }

                $salesOrder->calculateTotals();
            }
        }

        $this->command->info('Inventory data seeded successfully!');
    }
}
