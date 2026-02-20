<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LowStockReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Collection
    {
        return Product::with(['stocks', 'suppliers'])
            ->whereHas('stocks', function ($q) {
                $q->whereRaw('qty_on_hand <= products.min_stock');
            })
            ->orWhereDoesntHave('stocks')
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->stocks->sum('qty_on_hand');

                return $currentStock <= $product->min_stock;
            })
            ->map(function ($product) {
                $currentStock = $product->stocks->sum('qty_on_hand');
                $shortage = $product->min_stock - $currentStock;

                return (object) [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'min_stock' => $product->min_stock,
                    'current_stock' => $currentStock,
                    'shortage' => max(0, $shortage),
                    'supplier' => $product->suppliers->first()?->name ?? 'N/A',
                    'status' => $currentStock == 0 ? 'Out of Stock' : 'Low Stock',
                ];
            });
    }

    public function headings(): array
    {
        return ['SKU', 'Product Name', 'Min Stock', 'Current Stock', 'Shortage', 'Supplier', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->name,
            $row->min_stock,
            $row->current_stock,
            $row->shortage,
            $row->supplier,
            $row->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
