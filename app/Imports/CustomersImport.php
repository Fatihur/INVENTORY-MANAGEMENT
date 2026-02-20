<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Customer([
            'name' => $row['name'],
            'code' => $row['code'] ?? $this->generateCode(),
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'tax_id' => $row['tax_id'] ?? null,
            'credit_limit' => $row['credit_limit'] ?? 0,
            'payment_terms' => $row['payment_terms'] ?? 0,
            'is_active' => !isset($row['is_active']) || strtolower($row['is_active']) !== 'no',
            'notes' => $row['notes'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Customer name is required.',
            'email.email' => 'Email must be a valid email address.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
        ];
    }

    protected function generateCode(): string
    {
        $prefix = 'CUST';
        $lastCustomer = Customer::where('code', 'like', "{$prefix}%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->code, strlen($prefix));
            return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }
}
