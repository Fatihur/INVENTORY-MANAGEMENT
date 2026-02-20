<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'scanner_enabled',
                'value' => '1',
            ],
            [
                'key' => 'scanner_timeout',
                'value' => '50',
            ],
            [
                'key' => 'scanner_suffix',
                'value' => 'Enter',
            ],
        ];

        foreach ($settings as $set) {
            Setting::firstOrCreate([
                'key' => $set['key']
            ], $set);
        }
    }
}
