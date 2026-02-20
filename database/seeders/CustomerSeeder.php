<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customersData = [
            [
                'code' => 'CUST-001',
                'name' => 'PT. Maju Jaya Raya',
                'email' => 'contact@majujayaraya.com',
                'phone' => '081112223344',
                'tax_id' => '05.678.901.2-015.000',
                'credit_limit' => 100000000,
                'payment_terms' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-002',
                'name' => 'Toko Harapan Kita',
                'email' => 'admin@harapankita.com',
                'phone' => '082233445566',
                'tax_id' => null,
                'credit_limit' => 25000000,
                'payment_terms' => 14,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-003',
                'name' => 'CV. Sentosa Makmur',
                'email' => 'info@sentosamakmur.co.id',
                'phone' => '081999888777',
                'tax_id' => '06.789.012.3-044.000',
                'credit_limit' => 50000000,
                'payment_terms' => 45,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-004',
                'name' => 'Bapak Budi Retail',
                'email' => 'budiretail@gmail.com',
                'phone' => '081234567890',
                'tax_id' => null,
                'credit_limit' => 5000000,
                'payment_terms' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-005',
                'name' => 'PT. Teknologi Masa Depan',
                'email' => 'procurement@tekmad.com',
                'phone' => '021-99887766',
                'tax_id' => '07.890.123.4-055.000',
                'credit_limit' => 500000000,
                'payment_terms' => 60,
                'is_active' => true,
            ]
        ];

        foreach ($customersData as $data) {
            $customer = Customer::firstOrCreate(['code' => $data['code']], $data);
            
            if ($customer->wasRecentlyCreated) {
                // Add default billing address
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'type' => 'billing',
                    'address_line1' => 'Jl. Kebon Jeruk No. ' . rand(1, 100),
                    'city' => 'Jakarta Barat',
                    'state' => 'DKI Jakarta',
                    'postal_code' => '11530',
                    'country' => 'Indonesia',
                    'is_default' => true,
                ]);

                // Add default shipping address
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'type' => 'shipping',
                    'address_line1' => 'Pergudangan Bandara Mas Blok ' . chr(rand(65, 90)),
                    'city' => 'Tangerang',
                    'state' => 'Banten',
                    'postal_code' => '15121',
                    'country' => 'Indonesia',
                    'is_default' => true,
                ]);
            }
        }
    }
}
