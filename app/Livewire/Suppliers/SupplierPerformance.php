<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;

class SupplierPerformance extends Component
{
    public Supplier $supplier;

    public function mount(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-performance', [
            'purchaseOrders' => $this->supplier->purchaseOrders()->latest()->take(10)->get(),
            'onTimeRate' => $this->supplier->getOnTimeDeliveryRate(),
            'avgLeadTime' => $this->supplier->getAverageLeadTime(),
        ]);
    }
}
