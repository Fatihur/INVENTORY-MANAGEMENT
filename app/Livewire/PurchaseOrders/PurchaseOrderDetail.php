<?php

namespace App\Livewire\PurchaseOrders;

use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Models\PurchaseOrder;
use Livewire\Component;

class PurchaseOrderDetail extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function sendForApproval(PurchaseOrderServiceInterface $poService)
    {
        try {
            $poService->submitForApproval($this->purchaseOrder->id);
            $this->purchaseOrder->refresh();
            session()->flash('message', 'Purchase Order sent for approval.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function approve(PurchaseOrderServiceInterface $poService)
    {
        try {
            $poService->approve($this->purchaseOrder->id, auth()->id());
            $this->purchaseOrder->refresh();
            session()->flash('message', 'Purchase Order approved successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-orders.purchase-order-detail');
    }
}
