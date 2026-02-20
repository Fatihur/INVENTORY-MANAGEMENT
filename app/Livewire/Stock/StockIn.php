<?php

namespace App\Livewire\Stock;

use App\Contracts\Services\StockServiceInterface;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;

class StockIn extends Component
{
    public ?int $productId = null;
    public ?int $warehouseId = null;
    public int $quantity = 1;
    public float $unitCost = 0;
    public string $notes = '';
    public string $statusMessage = '';

    public function save(StockServiceInterface $stockService)
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'warehouseId' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'unitCost' => 'required|numeric|min:0',
        ]);

        $stockService->stockIn(
            $this->productId,
            $this->warehouseId,
            $this->quantity,
            $this->unitCost,
            $this->notes,
            auth()->id()
        );

        $this->reset(['productId', 'quantity', 'unitCost', 'notes']);
        $this->statusMessage = 'Stock in recorded successfully.';
    }

    public function render()
    {
        return view('livewire.stock.stock-in', [
            'products' => Product::active()->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
