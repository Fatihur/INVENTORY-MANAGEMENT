<?php

namespace App\Livewire\Suppliers;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierList extends Component
{
    use WithPagination;

    public string $search = '';

    public function render(SupplierRepositoryInterface $supplierRepository)
    {
        return view('livewire.suppliers.supplier-list', [
            'suppliers' => $supplierRepository->paginate(15),
        ]);
    }
}
