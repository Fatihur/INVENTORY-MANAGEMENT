<?php

namespace App\Livewire\Stock;

use App\Contracts\Services\StockServiceInterface;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Livewire\Component;

class StockTransfer extends Component
{
    public ?int $productId = null;
    public ?int $fromWarehouseId = null;
    public ?int $toWarehouseId = null;
    public int $quantity = 1;
    public string $notes = '';
    public string $statusMessage = '';
    public string $statusType = '';

    public function transfer(StockServiceInterface $stockService)
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'fromWarehouseId' => 'required|exists:warehouses,id',
            'toWarehouseId' => 'required|exists:warehouses,id|different:fromWarehouseId',
            'quantity' => 'required|integer|min:1',
        ]);

        // REMOVED: Pre-check validation that created TOCTOU vulnerability
        // The stock check is now done atomically within the service transaction
        // This prevents race conditions where stock could change between check and transfer

        try {
            $stockService->transfer(
                $this->productId,
                $this->fromWarehouseId,
                $this->toWarehouseId,
                $this->quantity,
                auth()->id()
            );

            $this->reset(['productId', 'fromWarehouseId', 'toWarehouseId', 'quantity', 'notes']);
            $this->statusMessage = 'Stock transferred successfully';
            $this->statusType = 'success';
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors (insufficient stock, same warehouse, etc.)
            $this->statusMessage = $e->getMessage();
            $this->statusType = 'error';
        } catch (\Exception $e) {
            $this->statusMessage = 'Transfer failed: ' . $e->getMessage();
            $this->statusType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.stock.stock-transfer', [
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
