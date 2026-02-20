<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-001',
                'name' => 'PT. Sumber Elektronik Makmur',
                'contact_person' => 'Budi Santoso',
                'email' => 'sales@sumberelektronik.co.id',
                'phone' => '021-5551234',
                'address' => 'Jl. Mangga Dua Raya No. 10, Jakarta Pusat',
                'default_payment_terms' => 30, // 30 days
                'default_lead_time_days' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'CV. Jaya Logistik',
                'contact_person' => 'Siti Aminah',
                'email' => 'info@jayalogistik.com',
                'phone' => '021-4449876',
                'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
                'default_payment_terms' => 14,
                'default_lead_time_days' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'Global Office Supplies Ltd.',
                'contact_person' => 'Michael Chang',
                'email' => 'orders@globaloffice.com',
                'phone' => '+65-6789-0123',
                'address' => '10 Changi Business Park, Singapore',
                'default_payment_terms' => 45,
                'default_lead_time_days' => 14,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-004',
                'name' => 'PT. Aneka Kimia Nusantara',
                'contact_person' => 'Agus Wijaya',
                'email' => 'agus.w@anekakimia.co.id',
                'phone' => '022-7771122',
                'address' => 'Jl. Soekarno Hatta No. 45, Bandung',
                'default_payment_terms' => 30,
                'default_lead_time_days' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'SUP-005',
                'name' => 'CV. Makmur Plastindo',
                'contact_person' => 'Dewi Lestari',
                'email' => 'cs@makmurplastik.com',
                'phone' => '031-8883344',
                'address' => 'Margomulyo, Surabaya',
                'default_payment_terms' => 0, // Cash
                'default_lead_time_days' => 2,
                'is_active' => true,
            ]
        ];

        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['code' => $sup['code']], $sup);
        }
    }
}
