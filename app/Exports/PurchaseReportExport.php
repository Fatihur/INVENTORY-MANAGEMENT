<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        $query = PurchaseOrder::with(['supplier', 'items']);

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('order_date', [$this->dateFrom, $this->dateTo]);
        }

        return $query->orderBy('order_date', 'desc')->get();
    }

    public function headings(): array
    {
        return ['PO Number', 'Supplier', 'Order Date', 'Expected Delivery', 'Subtotal', 'Tax', 'Total', 'Status'];
    }

    public function map($order): array
    {
        return [
            $order->po_number,
            $order->supplier?->name ?? 'N/A',
            $order->order_date?->format('Y-m-d'),
            $order->expected_delivery_date?->format('Y-m-d'),
            $order->subtotal,
            $order->tax_amount,
            $order->total_amount,
            $order->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
