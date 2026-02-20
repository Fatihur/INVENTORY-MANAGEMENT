<?php

namespace App\Livewire\PurchaseOrders;

use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function render(PurchaseOrderRepositoryInterface $poRepository)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->status,
        ];

        return view('livewire.purchase-orders.purchase-order-list', [
            'purchaseOrders' => $poRepository->getWithFilters($filters),
        ]);
    }
}
