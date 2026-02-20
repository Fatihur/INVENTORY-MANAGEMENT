<?php

namespace App\Livewire\Warehouses;

use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class WarehouseList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showModal = false;
    public $showDeleteModal = false;
    public $warehouseId = null;

    public $code = '';
    public $name = '';
    public $address = '';
    public $is_active = true;

    protected $rules = [
        'code' => 'required|string|max:50|unique:warehouses,code',
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    protected $listeners = ['refreshWarehouses' => '$refresh'];

    public function render()
    {
        $query = Warehouse::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== 'all') {
            $query->where('is_active', $this->status === 'active');
        }

        $warehouses = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.warehouses.warehouse-list', [
            'warehouses' => $warehouses
        ])->title('Warehouses - Inventory Management');
    }

    public function resetForm()
    {
        $this->reset([
            'warehouseId', 'code', 'name', 'address', 'is_active'
        ]);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $warehouse = Warehouse::findOrFail($id);

        $this->warehouseId = $warehouse->id;
        $this->code = $warehouse->code;
        $this->name = $warehouse->name;
        $this->address = $warehouse->address;
        $this->is_active = $warehouse->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        if ($this->warehouseId) {
            $this->rules['code'] = 'required|string|max:50|unique:warehouses,code,' . $this->warehouseId;
        }

        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];

        if ($this->warehouseId) {
            $warehouse = Warehouse::find($this->warehouseId);
            $warehouse->update($data);
            $this->dispatch('warehouse-updated');
        } else {
            Warehouse::create($data);
            $this->dispatch('warehouse-created');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->warehouseId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $warehouse = Warehouse::find($this->warehouseId);
        if ($warehouse) {
            // Check if warehouse has stocks
            if ($warehouse->stocks()->count() > 0) {
                $this->dispatch('error', message: 'Cannot delete warehouse with existing stocks');
                $this->showDeleteModal = false;
                return;
            }
            $warehouse->delete();
            $this->dispatch('warehouse-deleted');
        }
        $this->showDeleteModal = false;
        $this->warehouseId = null;
    }

    public function toggleStatus($id)
    {
        $warehouse = Warehouse::find($id);
        if ($warehouse) {
            $warehouse->update(['is_active' => !$warehouse->is_active]);
            $this->dispatch('warehouse-updated');
        }
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }
}
