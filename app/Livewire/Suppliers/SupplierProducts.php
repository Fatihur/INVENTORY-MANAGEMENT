<?php

namespace App\Livewire\Suppliers;

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;

class SupplierProducts extends Component
{
    public Supplier $supplier;
    public ?int $selectedProductId = null;
    public float $buyPrice = 0;
    public int $moq = 1;
    public int $leadTimeDays = 7;
    public string $statusMessage = '';

    public function mount(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function addProduct()
    {
        $this->validate([
            'selectedProductId' => 'required|exists:products,id',
            'buyPrice' => 'required|numeric|min:0',
            'moq' => 'required|integer|min:1',
            'leadTimeDays' => 'required|integer|min:1',
        ]);

        // Check if already exists
        $exists = $this->supplier->products()
            ->where('product_id', $this->selectedProductId)
            ->exists();

        if ($exists) {
            // Update existing
            $this->supplier->products()->updateExistingPivot($this->selectedProductId, [
                'buy_price' => $this->buyPrice,
                'moq' => $this->moq,
                'lead_time_days' => $this->leadTimeDays,
            ]);
            $this->statusMessage = 'Product updated successfully';
        } else {
            // Add new
            $this->supplier->products()->attach($this->selectedProductId, [
                'buy_price' => $this->buyPrice,
                'moq' => $this->moq,
                'lead_time_days' => $this->leadTimeDays,
                'is_primary' => false,
            ]);
            $this->statusMessage = 'Product added successfully';
        }

        $this->reset(['selectedProductId', 'buyPrice', 'moq', 'leadTimeDays']);
    }

    public function removeProduct(int $productId)
    {
        $this->supplier->products()->detach($productId);
        $this->statusMessage = 'Product removed successfully';
    }

    public function setPrimary(int $productId)
    {
        // Remove primary from all products
        \DB::table('supplier_products')
            ->where('supplier_id', $this->supplier->id)
            ->update(['is_primary' => false]);

        // Set new primary
        \DB::table('supplier_products')
            ->where('supplier_id', $this->supplier->id)
            ->where('product_id', $productId)
            ->update(['is_primary' => true]);

        $this->statusMessage = 'Primary supplier updated';
    }

    public function render()
    {
        $existingProductIds = $this->supplier->products()->pluck('product_id')->toArray();

        return view('livewire.suppliers.supplier-products', [
            'supplierProducts' => $this->supplier->products()->withPivot('buy_price', 'moq', 'lead_time_days', 'is_primary')->get(),
            'availableProducts' => Product::where('is_active', true)
                ->whereNotIn('id', $existingProductIds)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
