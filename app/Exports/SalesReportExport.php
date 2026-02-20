<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $dateFrom;

    protected $dateTo;

    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection(): Collection
    {
        $query = SalesOrder::with(['customer', 'items']);

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('order_date', [$this->dateFrom, $this->dateTo]);
        }

        return $query->orderBy('order_date', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Order Number', 'Customer', 'Order Date', 'Delivery Date', 'Total Amount', 'Discount', 'Status', 'Notes'];
    }

    public function map($order): array
    {
        return [
            $order->so_number,
            $order->customer?->name ?? 'N/A',
            $order->order_date?->format('Y-m-d'),
            $order->delivery_date?->format('Y-m-d'),
            $order->total_amount,
            $order->discount_amount,
            $order->status,
            $order->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
