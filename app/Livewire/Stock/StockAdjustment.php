<?php

namespace App\Livewire\Stock;

use App\Contracts\Services\StockServiceInterface;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Livewire\Component;

class StockAdjustment extends Component
{
    public ?int $productId = null;
    public ?int $warehouseId = null;
    public ?int $newQty = null;
    public string $reason = '';
    public string $statusMessage = '';
    public string $statusType = '';

    public $currentStock = null;

    public function updated($property)
    {
        if (in_array($property, ['productId', 'warehouseId'])) {
            $this->updateCurrentStock();
        }
    }

    private function updateCurrentStock()
    {
        if ($this->productId && $this->warehouseId) {
            $stock = Stock::where([
                'product_id' => $this->productId,
                'warehouse_id' => $this->warehouseId
            ])->first();

            $this->currentStock = $stock?->qty_on_hand ?? 0;
            $this->newQty = $this->currentStock;
        } else {
            $this->currentStock = null;
            $this->newQty = null;
        }
    }

    public function adjust(StockServiceInterface $stockService)
    {
        // Authorization check
        if (!auth()->user()->can('stock.adjust')) {
            $this->statusMessage = 'You do not have permission to adjust stock';
            $this->statusType = 'error';
            return;
        }

        $this->validate([
            'productId' => 'required|exists:products,id',
            'warehouseId' => 'required|exists:warehouses,id',
            'newQty' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $stockService->adjustStock(
                $this->productId,
                $this->warehouseId,
                $this->newQty,
                $this->reason,
                auth()->id()
            );

            $this->reset(['productId', 'warehouseId', 'newQty', 'reason']);
            $this->currentStock = null;
            $this->statusMessage = 'Stock adjusted successfully';
            $this->statusType = 'success';
        } catch (\InvalidArgumentException $e) {
            $this->statusMessage = $e->getMessage();
            $this->statusType = 'error';
        } catch (\Exception $e) {
            $this->statusMessage = 'Adjustment failed: ' . $e->getMessage();
            $this->statusType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.stock.stock-adjustment', [
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
