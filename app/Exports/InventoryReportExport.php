<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $productFilter;

    public function __construct($productFilter = 'all')
    {
        $this->productFilter = $productFilter;
    }

    public function collection(): Collection
    {
        $query = Product::with(['stocks']);

        if ($this->productFilter && $this->productFilter !== 'all') {
            $query->where('id', $this->productFilter);
        }

        return $query->get()->map(function ($product) {
            $stockQty = $product->stocks->sum('qty_on_hand');

            return (object) [
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category,
                'stock_qty' => $stockQty,
                'purchase_price' => $product->cost_price ?? 0,
                'stock_value' => $stockQty * ($product->cost_price ?? 0),
                'min_stock' => $product->min_stock,
                'status' => $stockQty == 0 ? 'Out of Stock' : ($stockQty <= $product->min_stock ? 'Low Stock' : 'OK'),
            ];
        });
    }

    public function headings(): array
    {
        return ['SKU', 'Product Name', 'Category', 'Stock Qty', 'Unit Price', 'Stock Value', 'Min Stock', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->name,
            $row->category ?? '-',
            $row->stock_qty,
            $row->purchase_price,
            $row->stock_value,
            $row->min_stock,
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
