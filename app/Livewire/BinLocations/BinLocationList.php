<?php

namespace App\Livewire\BinLocations;

use App\Models\BinLocation;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class BinLocationList extends Component
{
    use WithPagination;

    public $search = '';

    public $warehouseFilter = 'all';

    public $capacityFilter = 'all';

    public $statusFilter = 'all';

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $perPage = 10;

    public $showModal = false;

    public $showViewModal = false;

    public $showDeleteModal = false;

    public $binId = null;

    public $warehouse_id_input = '';

    public $zone = '';

    public $aisle = '';

    public $rack = '';

    public $shelf = '';

    public $bin = '';

    public $capacity = '';

    public $is_active = true;

    protected $rules = [
        'warehouse_id_input' => 'required|exists:warehouses,id',
        'zone' => 'required|string|max:10',
        'aisle' => 'required|string|max:10',
        'rack' => 'required|string|max:10',
        'shelf' => 'required|string|max:10',
        'bin' => 'required|string|max:10',
        'capacity' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = BinLocation::with(['warehouse']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('zone', 'like', "%{$this->search}%")
                    ->orWhere('aisle', 'like', "%{$this->search}%")
                    ->orWhere('rack', 'like', "%{$this->search}%");
            });
        }

        if ($this->warehouseFilter !== 'all') {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if ($this->capacityFilter === 'empty') {
            $query->where('current_qty', 0);
        } elseif ($this->capacityFilter === 'full') {
            $query->whereRaw('current_qty >= capacity');
        } elseif ($this->capacityFilter === 'available') {
            $query->whereRaw('current_qty < capacity');
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $binLocations = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $warehousesList = Warehouse::where('is_active', true)->get();

        return view('livewire.bin-locations.bin-location-list', [
            'binLocations' => $binLocations,
            'warehousesList' => $warehousesList,
        ])->title('Bin Locations - Inventory Management');
    }

    public function getBinCodeProperty()
    {
        return strtoupper($this->zone.'-'.$this->aisle.'-'.$this->rack.'-'.$this->shelf.'-'.$this->bin);
    }

    public function getCapacityPercentage($bin)
    {
        if (! $bin->capacity || $bin->capacity == 0) {
            return 0;
        }

        return min(100, ($bin->current_qty / $bin->capacity) * 100);
    }

    public function resetForm()
    {
        $this->reset([
            'binId', 'warehouse_id_input', 'zone', 'aisle', 'rack', 'shelf', 'bin', 'capacity', 'is_active',
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
        $bin = BinLocation::findOrFail($id);

        $this->binId = $bin->id;
        $this->warehouse_id_input = $bin->warehouse_id;
        $this->zone = $bin->zone;
        $this->aisle = $bin->aisle;
        $this->rack = $bin->rack;
        $this->shelf = $bin->shelf ?? $bin->level ?? '';
        $this->bin = $bin->bin ?? $bin->position ?? '';
        $this->capacity = $bin->capacity;
        $this->is_active = $bin->is_active;

        $this->showModal = true;
    }

    public function view($id)
    {
        $this->binId = $id;
        $this->showViewModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'warehouse_id' => $this->warehouse_id_input,
            'code' => $this->binCode,
            'zone' => strtoupper($this->zone),
            'aisle' => strtoupper($this->aisle),
            'rack' => strtoupper($this->rack),
            'shelf' => strtoupper($this->shelf),
            'bin' => strtoupper($this->bin),
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
        ];

        if ($this->binId) {
            $bin = BinLocation::find($this->binId);
            $bin->update($data);
            $this->dispatch('bin-updated');
        } else {
            BinLocation::create($data);
            $this->dispatch('bin-created');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->binId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $bin = BinLocation::find($this->binId);
        if ($bin) {
            if ($bin->current_qty > 0) {
                $this->dispatch('error', message: 'Cannot delete bin with existing stocks');
                $this->showDeleteModal = false;

                return;
            }
            $bin->delete();
            $this->dispatch('bin-deleted');
        }
        $this->showDeleteModal = false;
        $this->binId = null;
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

    public function updatedWarehouseFilter()
    {
        $this->resetPage();
    }

    public function updatedCapacityFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $bin = BinLocation::find($id);
        if ($bin) {
            $bin->update(['is_active' => ! $bin->is_active]);
            $this->dispatch('bin-updated');
        }
    }
}
