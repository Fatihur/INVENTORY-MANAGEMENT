<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Product::with(['stocks.warehouse']);

        if (! empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

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
            'SKU',
            'Category',
            'Unit',
            'Cost Price',
            'Selling Price',
            'Min Stock',
            'Total Stock',
            'Track Batch',
            'Track Serial',
            'Status',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->code,
            $product->name,
            $product->sku,
            $product->category ?? 'N/A',
            $product->unit,
            $product->cost_price,
            $product->selling_price,
            $product->min_stock,
            $product->stocks->sum('qty_on_hand'),
            $product->track_batch ? 'Yes' : 'No',
            $product->track_serial ? 'Yes' : 'No',
            $product->is_active ? 'Active' : 'Inactive',
            $product->created_at->format('Y-m-d H:i:s'),
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
        return 'Products';
    }
}
