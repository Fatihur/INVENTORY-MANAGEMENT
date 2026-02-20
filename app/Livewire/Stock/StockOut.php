<?php

namespace App\Livewire\Stock;

use App\Contracts\Services\StockServiceInterface;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;

class StockOut extends Component
{
    public ?int $productId = null;
    public ?int $warehouseId = null;
    public int $quantity = 1;
    public string $notes = '';
    public string $statusMessage = '';

    public function save(StockServiceInterface $stockService)
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'warehouseId' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $stockService->stockOut(
                $this->productId,
                $this->warehouseId,
                $this->quantity,
                $this->notes,
                auth()->id()
            );

            $this->reset(['productId', 'quantity', 'notes']);
            $this->statusMessage = 'Stock out recorded successfully.';
        } catch (\Exception $e) {
            $this->statusMessage = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.stock.stock-out', [
            'products' => Product::active()->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
