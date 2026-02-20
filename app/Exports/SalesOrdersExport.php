<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesOrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SalesOrder::with(['customer', 'items.product']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['date_from'])) {
            $query->where('order_date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->where('order_date', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'SO Number',
            'Customer',
            'Status',
            'Order Date',
            'Delivery Date',
            'Items Count',
            'Subtotal',
            'Tax',
            'Discount',
            'Total Amount',
            'Created At',
        ];
    }

    public function map($order): array
    {
        return [
            $order->so_number,
            $order->customer?->name ?? 'N/A',
            ucfirst($order->status),
            $order->order_date->format('Y-m-d'),
            $order->delivery_date?->format('Y-m-d') ?? 'N/A',
            $order->items->count(),
            $order->subtotal,
            $order->tax_amount,
            $order->discount_amount,
            $order->total_amount,
            $order->created_at->format('Y-m-d H:i:s'),
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
        return 'Sales Orders';
    }
}
