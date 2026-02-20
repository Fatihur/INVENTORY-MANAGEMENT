<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'],
            'code' => $row['code'] ?? $this->generateCode($row['name']),
            'sku' => $row['sku'] ?? null,
            'category_id' => $row['category_id'] ?? null,
            'description' => $row['description'] ?? null,
            'unit' => $row['unit'] ?? 'pcs',
            'cost_price' => $row['cost_price'] ?? 0,
            'selling_price' => $row['selling_price'] ?? 0,
            'min_stock' => $row['min_stock'] ?? 0,
            'max_stock' => $row['max_stock'] ?? 0,
            'track_batch' => isset($row['track_batch']) && strtolower($row['track_batch']) === 'yes',
            'track_serial' => isset($row['track_serial']) && strtolower($row['track_serial']) === 'yes',
            'is_active' => !isset($row['is_active']) || strtolower($row['is_active']) !== 'no',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:products,code',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Product name is required.',
            'cost_price.numeric' => 'Cost price must be a number.',
            'selling_price.numeric' => 'Selling price must be a number.',
        ];
    }

    protected function generateCode(string $name): string
    {
        $prefix = 'PROD';
        $lastProduct = Product::where('code', 'like', "{$prefix}%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->code, strlen($prefix));
            return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }
}
