<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Owner',
                'email' => 'owner@inventory.test',
                'password' => Hash::make('password'),
                'role' => UserRole::OWNER->value,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@inventory.test',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN->value,
            ],
            [
                'name' => 'Warehouse Staff',
                'email' => 'warehouse@inventory.test',
                'password' => Hash::make('password'),
                'role' => UserRole::WAREHOUSE->value,
            ],
            [
                'name' => 'Purchasing',
                'email' => 'purchasing@inventory.test',
                'password' => Hash::make('password'),
                'role' => UserRole::PURCHASING->value,
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@inventory.test',
                'password' => Hash::make('password'),
                'role' => UserRole::MANAGER->value,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->assignRole($role);
        }
    }
}
