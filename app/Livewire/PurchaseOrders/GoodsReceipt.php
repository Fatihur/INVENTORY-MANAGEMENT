<?php

namespace App\Livewire\PurchaseOrders;

use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Livewire\Component;

class GoodsReceipt extends Component
{
    public PurchaseOrder $purchaseOrder;
    public ?int $warehouseId = null;
    public string $invoiceNumber = '';
    public array $receiptItems = [];
    public string $statusMessage = '';

    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->warehouseId = Warehouse::where('is_active', true)->first()?->id;

        // Initialize receipt items from PO items
        foreach ($purchaseOrder->items as $item) {
            $this->receiptItems[] = [
                'po_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'qty_ordered' => $item->qty_ordered,
                'qty_received' => $item->qty_ordered - $item->qty_received,
                'batch_number' => '',
                'expiry_date' => null,
            ];
        }
    }

    protected function rules()
    {
        return [
            'warehouseId' => 'required|exists:warehouses,id',
            'invoiceNumber' => 'required|string|max:100',
            'receiptItems' => 'required|array',
            'receiptItems.*.qty_received' => 'required|integer|min:0',
            'receiptItems.*.batch_number' => 'nullable|string|max:50',
            'receiptItems.*.expiry_date' => 'nullable|date',
        ];
    }

    public function receive(PurchaseOrderServiceInterface $poService)
    {
        $this->validate();

        // Filter out items with 0 qty received
        $itemsToReceive = collect($this->receiptItems)
            ->filter(fn($item) => ($item['qty_received'] ?? 0) > 0)
            ->map(fn($item) => [
                'po_item_id' => $item['po_item_id'],
                'qty_received' => $item['qty_received'],
                'batch_number' => $item['batch_number'] ?: null,
                'expiry_date' => $item['expiry_date'] ?: null,
            ])
            ->toArray();

        if (empty($itemsToReceive)) {
            $this->statusMessage = 'Please enter quantity for at least one item.';
            return;
        }

        try {
            $poService->receiveGoods(
                $this->purchaseOrder->id,
                $itemsToReceive,
                $this->invoiceNumber,
                $this->warehouseId,
                auth()->id()
            );

            session()->flash('message', 'Goods received successfully.');
            return redirect()->route('purchase-orders.show', $this->purchaseOrder);
        } catch (\Exception $e) {
            $this->statusMessage = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.purchase-orders.goods-receipt', [
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }
}
