<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $sku = $row['sku'] ?? $this->generateSku($row['name']);

        return new Product([
            'name' => $row['name'],
            'code' => $row['code'] ?? $sku,
            'sku' => $sku,
            'category' => $row['category'] ?? null,
            'description' => $row['description'] ?? null,
            'unit' => $row['unit'] ?? 'pcs',
            'cost_price' => $row['cost_price'] ?? 0,
            'selling_price' => $row['selling_price'] ?? 0,
            'min_stock' => $row['min_stock'] ?? 0,
            'max_stock' => $row['max_stock'] ?? 0,
            'safety_stock' => $row['safety_stock'] ?? 0,
            'target_stock' => $row['target_stock'] ?? null,
            'lead_time_days' => $row['lead_time_days'] ?? 7,
            'track_batch' => isset($row['track_batch']) && strtolower($row['track_batch']) === 'yes',
            'track_serial' => isset($row['track_serial']) && strtolower($row['track_serial']) === 'yes',
            'is_active' => ! isset($row['is_active']) || strtolower($row['is_active']) !== 'no',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
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

    protected function generateSku(string $name): string
    {
        $prefix = Str::upper(Str::substr(Str::slug($name, ''), 0, 4));
        if ($prefix === '') {
            $prefix = 'PROD';
        }

        $lastProduct = Product::where('sku', 'like', "{$prefix}%")
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastProduct) {
            $lastNumber = (int) preg_replace('/\D/', '', $lastProduct->sku);

            return $prefix.str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix.'00001';
    }
}
