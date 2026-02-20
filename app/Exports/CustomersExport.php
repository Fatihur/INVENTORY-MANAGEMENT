<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Customer::with('addresses');

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Email',
            'Phone',
            'Tax ID',
            'Credit Limit',
            'Payment Terms',
            'City',
            'Country',
            'Status',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        $defaultAddress = $customer->addresses->first();

        return [
            $customer->id,
            $customer->code,
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->tax_id,
            $customer->credit_limit,
            $customer->payment_terms . ' days',
            $defaultAddress?->city ?? 'N/A',
            $defaultAddress?->country ?? 'N/A',
            $customer->is_active ? 'Active' : 'Inactive',
            $customer->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Customers';
    }
}
