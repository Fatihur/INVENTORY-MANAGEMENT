<?php

namespace Database\Seeders;

use App\Models\ApprovalLevel;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class ApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            // Sales Order Approvals
            [
                'name' => 'Manager Review',
                'model_type' => 'App\\Models\\SalesOrder',
                'level_order' => 1,
                'role_name' => 'manager',
                'is_active' => true,
            ],
            [
                'name' => 'Final Director Approval',
                'model_type' => 'App\\Models\\SalesOrder',
                'level_order' => 2,
                'role_name' => 'admin',
                'is_active' => true,
            ],
            // Purchase Order Approvals
            [
                'name' => 'Purchasing Manager',
                'model_type' => 'App\\Models\\PurchaseOrder',
                'level_order' => 1,
                'role_name' => 'manager',
                'is_active' => true,
            ],
            [
                'name' => 'Finance Director',
                'model_type' => 'App\\Models\\PurchaseOrder',
                'level_order' => 2,
                'role_name' => 'admin',
                'is_active' => true,
            ],
        ];

        foreach ($levels as $level) {
            $role = Role::where('name', $level['role_name'])->first();

            if ($role) {
                ApprovalLevel::firstOrCreate([
                    'name' => $level['name'],
                    'model_type' => $level['model_type']
                ], [
                    'level_order' => $level['level_order'],
                    'role_id' => $role->id,
                    'is_active' => $level['is_active'],
                ]);
            }
        }
    }
}
