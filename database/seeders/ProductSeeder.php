<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Electronics
            ['sku' => 'ELC-LAP-001', 'name' => 'ThinkPad T14 Gen 3', 'category' => 'Electronics', 'unit' => 'pcs', 'min_stock' => 10, 'safety_stock' => 5, 'target_stock' => 50, 'cost_price' => 15000000, 'selling_price' => 18500000],
            ['sku' => 'ELC-MON-001', 'name' => 'Dell UltraSharp 27 Monitor', 'category' => 'Electronics', 'unit' => 'pcs', 'min_stock' => 20, 'safety_stock' => 10, 'target_stock' => 100, 'cost_price' => 5000000, 'selling_price' => 6500000],
            ['sku' => 'ELC-KBD-001', 'name' => 'Logitech MX Keys Wireless', 'category' => 'Electronics', 'unit' => 'pcs', 'min_stock' => 30, 'safety_stock' => 15, 'target_stock' => 150, 'cost_price' => 1200000, 'selling_price' => 1800000],
            ['sku' => 'ELC-MSE-001', 'name' => 'Logitech MX Master 3S', 'category' => 'Electronics', 'unit' => 'pcs', 'min_stock' => 30, 'safety_stock' => 15, 'target_stock' => 150, 'cost_price' => 1100000, 'selling_price' => 1600000],
            
            // Office Supplies
            ['sku' => 'OFC-PAP-001', 'name' => 'HVS A4 80gsm PaperOne (Rim)', 'category' => 'Office Supplies', 'unit' => 'rim', 'min_stock' => 100, 'safety_stock' => 50, 'target_stock' => 500, 'cost_price' => 45000, 'selling_price' => 55000],
            ['sku' => 'OFC-PEN-001', 'name' => 'Pilot Pen Black (Box of 12)', 'category' => 'Office Supplies', 'unit' => 'box', 'min_stock' => 200, 'safety_stock' => 100, 'target_stock' => 1000, 'cost_price' => 20000, 'selling_price' => 30000],
            ['sku' => 'OFC-FLR-001', 'name' => 'Bantex Ordner Folio Blue', 'category' => 'Office Supplies', 'unit' => 'pcs', 'min_stock' => 50, 'safety_stock' => 20, 'target_stock' => 200, 'cost_price' => 18000, 'selling_price' => 25000],
            
            // Packaging
            ['sku' => 'PKG-BOX-001', 'name' => 'Kardus Polos 30x30x30 cm', 'category' => 'Packaging', 'unit' => 'pcs', 'min_stock' => 500, 'safety_stock' => 200, 'target_stock' => 2000, 'cost_price' => 3000, 'selling_price' => 5000],
            ['sku' => 'PKG-TPE-001', 'name' => 'Lakban Bening 2 inch Daimaru', 'category' => 'Packaging', 'unit' => 'roll', 'min_stock' => 100, 'safety_stock' => 50, 'target_stock' => 500, 'cost_price' => 8000, 'selling_price' => 12000],
            ['sku' => 'PKG-BRP-001', 'name' => 'Bubble Wrap Hitam 50m x 125cm', 'category' => 'Packaging', 'unit' => 'roll', 'min_stock' => 50, 'safety_stock' => 10, 'target_stock' => 200, 'cost_price' => 135000, 'selling_price' => 180000],

            // Raw Material (Chemicals)
            ['sku' => 'RAW-CHM-001', 'name' => 'Isopropyl Alcohol 99% (Drum 200L)', 'category' => 'Raw Material', 'unit' => 'drum', 'min_stock' => 5, 'safety_stock' => 2, 'target_stock' => 20, 'track_batch' => true, 'cost_price' => 4500000, 'selling_price' => 6000000],
            ['sku' => 'RAW-CHM-002', 'name' => 'Glycerin USP Grade (Jerrycan 25L)', 'category' => 'Raw Material', 'unit' => 'can', 'min_stock' => 20, 'safety_stock' => 5, 'target_stock' => 50, 'track_batch' => true, 'cost_price' => 750000, 'selling_price' => 1000000],
        ];

        $suppliers = Supplier::all();

        foreach ($products as $prodData) {
            $costPrice = $prodData['cost_price'];
            $sellingPrice = $prodData['selling_price'];
            unset($prodData['cost_price'], $prodData['selling_price']);

            $product = Product::firstOrCreate(['sku' => $prodData['sku']], $prodData);

            if ($product->wasRecentlyCreated && $suppliers->count() > 0) {
                // Attach random supplier
                $product->suppliers()->attach($suppliers->random()->id, [
                    'is_primary' => true,
                    'buy_price' => $costPrice,
                    'moq' => rand(1, 10),
                    'lead_time_days_override' => rand(2, 14),
                    'supplier_sku' => 'SUP-' . $product->sku
                ]);

                // Manually set selling price attribute (though typically logic handles this or we inject stock with price)
                // Assuming we might have standard price columns if requested, otherwise handled during PO/SO. 
                // Wait, product model does not have default selling_price? 
                // Ah, the schema might not have it or it uses stock cost. I'll add them as properties if they exist, else skip.
                if (\Schema::hasColumn('products', 'cost_price')) {
                    $product->cost_price = $costPrice;
                    $product->selling_price = $sellingPrice;
                    $product->save();
                }
            }
        }
    }
}
