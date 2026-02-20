<?php

namespace App\Livewire\Stock;

use App\Contracts\Services\QrCodeServiceInterface;
use App\Contracts\Services\StockServiceInterface;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;

class StockScanner extends Component
{
    public string $scanMode = 'view';
    public ?string $scannedCode = null;
    public ?Product $product = null;
    public array $productData = [];
    public int $quantity = 1;
    public string $notes = '';
    public ?int $warehouseId = null;
    public string $statusMessage = '';
    public string $statusType = '';

    public function mount()
    {
        $this->warehouseId = Warehouse::where('is_active', true)->first()?->id;
    }

    public function codeScanned(string $code, QrCodeServiceInterface $qrService)
    {
        $this->scannedCode = $code;
        $decoded = $qrService->decode($code);

        if (!$decoded) {
            $this->setStatus('Invalid QR code', 'error');
            return;
        }

        $this->loadProduct($decoded);
    }

    private function loadProduct(array $decoded)
    {
        $product = match ($decoded['type']) {
            'PROD' => Product::with(['stocks.warehouse', 'suppliers'])->find($decoded['id'] ?? null),
            'BATCH' => Product::with(['stocks.warehouse', 'suppliers'])->find($decoded['id'] ?? null),
            default => null,
        };

        if (!$product) {
            $this->setStatus('Product not found', 'error');
            return;
        }

        if (!$product->is_active) {
            $this->setStatus('Warning: Product is discontinued', 'warning');
        }

        $this->product = $product;
        $this->productData = [
            'name' => $product->name,
            'sku' => $product->sku,
            'total_stock' => $product->total_stock,
            'min_stock' => $product->min_stock,
            'location' => $product->stocks->first()?->warehouse?->code ?? 'N/A',
            'supplier' => $product->primarySupplier?->name ?? 'N/A',
        ];
    }

    public function processTransaction(StockServiceInterface $stockService)
    {
        if (!$this->product || !$this->warehouseId) {
            $this->setStatus('Invalid product or warehouse', 'error');
            return;
        }

        try {
            match ($this->scanMode) {
                'in' => $stockService->stockIn(
                    $this->product->id,
                    $this->warehouseId,
                    $this->quantity,
                    0,
                    $this->notes,
                    auth()->id()
                ),
                'out' => $stockService->stockOut(
                    $this->product->id,
                    $this->warehouseId,
                    $this->quantity,
                    $this->notes,
                    auth()->id()
                ),
                default => null,
            };

            $this->reset(['scannedCode', 'product', 'productData', 'quantity', 'notes']);
            $this->setStatus('Transaction completed successfully', 'success');
        } catch (\Exception $e) {
            $this->setStatus($e->getMessage(), 'error');
        }
    }

    public function resetScan()
    {
        $this->reset(['scannedCode', 'product', 'productData', 'quantity', 'notes', 'statusMessage', 'statusType']);
    }

    private function setStatus(string $message, string $type)
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }

    public function render()
    {
        return view('livewire.stock.stock-scanner', [
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
