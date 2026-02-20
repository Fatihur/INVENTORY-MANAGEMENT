<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['code' => 'MAIN', 'name' => 'Gudang Utama', 'address' => 'Jl. Raya No. 1'],
            ['code' => 'A1', 'name' => 'Rak A1', 'address' => 'Area A, Rak 1'],
            ['code' => 'A2', 'name' => 'Rak A2', 'address' => 'Area A, Rak 2'],
            ['code' => 'B1', 'name' => 'Rak B1', 'address' => 'Area B, Rak 1'],
            ['code' => 'B2', 'name' => 'Rak B2', 'address' => 'Area B, Rak 2'],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
