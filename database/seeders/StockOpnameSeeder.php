<?php

namespace Database\Seeders;

use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockOpnameSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $user = User::first();

        if ($warehouses->isEmpty() || !$user) {
            return;
        }

        $mainWh = $warehouses->first();

        $statuses = ['draft', 'in_progress', 'completed'];

        for ($i = 1; $i <= 3; $i++) {
            $status = $statuses[$i - 1]; // Cycle through statuses
            
            StockOpname::create([
                'doc_number' => 'OPN-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'warehouse_id' => $mainWh->id,
                'status' => $status,
                'type' => 'partial',
                'scheduled_date' => now()->subDays(rand(1, 14)),
                'start_time' => $status !== 'draft' ? now()->subDays(rand(1, 14)) : null,
                'end_time' => $status === 'completed' ? now()->subDays(rand(1, 14))->addHours(2) : null,
                'notes' => 'Seeded Stock Opname routine check',
                'created_by' => $user->id,
            ]);
        }
    }
}
