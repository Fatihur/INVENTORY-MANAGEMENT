<?php

namespace App\Livewire\Batches;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class BatchList extends Component
{
    use WithPagination;

    public $search = '';
    public $productFilter = 'all';
    public $warehouseFilter = 'all';
    public $expiryFilter = 'all';
    public $statusFilter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public $batchId = null;

    public $product_id = '';
    public $batch_number = '';
    public $manufacturing_date = '';
    public $expiry_date = '';
    public $initial_qty = 0;
    public $remaining_qty = 0;
    public $warehouse_id = '';
    public $cost_price = '';
    public $notes = '';
    public $is_active = true;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'batch_number' => 'required|string|max:100',
        'manufacturing_date' => 'nullable|date',
        'expiry_date' => 'nullable|date',
        'initial_qty' => 'required|numeric|min:0',
        'remaining_qty' => 'required|numeric|min:0',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'cost_price' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = Batch::with(['product', 'warehouse']);

        if ($this->search) {
            $query->where('batch_number', 'like', "%{$this->search}%");
        }

        if ($this->productFilter !== 'all') {
            $query->where('product_id', $this->productFilter);
        }

        if ($this->warehouseFilter !== 'all') {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if ($this->expiryFilter === 'expired') {
            $query->where('expiry_date', '<', now());
        } elseif ($this->expiryFilter === 'expiring_30') {
            $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
        } elseif ($this->expiryFilter === 'expiring_60') {
            $query->whereBetween('expiry_date', [now(), now()->addDays(60)]);
        } elseif ($this->expiryFilter === 'expiring_90') {
            $query->whereBetween('expiry_date', [now(), now()->addDays(90)]);
        } elseif ($this->expiryFilter === 'no_expiry') {
            $query->whereNull('expiry_date');
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $batches = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $products = Product::orderBy('name')->get();
        $warehousesList = Warehouse::where('is_active', true)->get();

        return view('livewire.batches.batch-list', [
            'batches' => $batches,
            'products' => $products,
            'warehousesList' => $warehousesList,
        ])->title('Batches - Inventory Management');
    }

    public function resetForm()
    {
        $this->reset([
            'batchId', 'product_id', 'batch_number', 'manufacturing_date', 'expiry_date',
            'initial_qty', 'remaining_qty', 'warehouse_id', 'cost_price', 'notes', 'is_active'
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
        $batch = Batch::findOrFail($id);

        $this->batchId = $batch->id;
        $this->product_id = $batch->product_id;
        $this->batch_number = $batch->batch_number;
        $this->manufacturing_date = $batch->manufacturing_date?->format('Y-m-d');
        $this->expiry_date = $batch->expiry_date?->format('Y-m-d');
        $this->initial_qty = $batch->initial_qty ?? $batch->remaining_qty ?? 0;
        $this->remaining_qty = $batch->remaining_qty ?? 0;
        $this->warehouse_id = $batch->warehouse_id ?? '';
        $this->cost_price = $batch->cost_price ?? '';
        $this->notes = $batch->notes;
        $this->is_active = $batch->is_active ?? true;

        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->batchId) {
            $rules['batch_number'] = 'required|string|max:100|unique:batches,batch_number,' . $this->batchId;
        } else {
            $rules['batch_number'] = 'required|string|max:100|unique:batches,batch_number';
        }

        $this->validate($rules);

        $data = [
            'product_id' => $this->product_id,
            'batch_number' => $this->batch_number,
            'manufacturing_date' => $this->manufacturing_date ?: null,
            'expiry_date' => $this->expiry_date ?: null,
            'initial_qty' => $this->initial_qty,
            'remaining_qty' => $this->remaining_qty,
            'warehouse_id' => $this->warehouse_id ?: null,
            'cost_price' => $this->cost_price ?: null,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ];

        if ($this->batchId) {
            $batch = Batch::find($this->batchId);
            $batch->update($data);
            $this->dispatch('batch-updated');
        } else {
            Batch::create($data);
            $this->dispatch('batch-created');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function view($id)
    {
        $this->batchId = $id;
        $this->showViewModal = true;
    }

    public function confirmDelete($id)
    {
        $this->batchId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $batch = Batch::find($this->batchId);
        if ($batch) {
            $batch->delete();
            $this->dispatch('batch-deleted');
        }
        $this->showDeleteModal = false;
        $this->batchId = null;
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

    public function updatedProductFilter()
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter()
    {
        $this->resetPage();
    }

    public function updatedExpiryFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $batch = Batch::find($id);
        if ($batch) {
            $batch->update(['is_active' => !$batch->is_active]);
            $this->dispatch('batch-updated');
        }
    }
}
