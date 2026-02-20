<?php

namespace App\Livewire\StockOpname;

use App\Models\StockOpname;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockOpnameList extends Component
{
    use WithPagination;

    public $search = '';
    public $warehouseFilter = 'all';
    public $statusFilter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showModal = false;
    public $showViewModal = false;
    public $showCompleteModal = false;
    public $showAdjustModal = false;
    public $showDeleteModal = false;
    public $opnameId = null;

    public $warehouse_id = '';
    public $product_id = '';
    public $system_qty = 0;
    public $actual_qty = 0;
    public $notes = '';

    protected $rules = [
        'warehouse_id' => 'required|exists:warehouses,id',
        'product_id' => 'required|exists:products,id',
        'actual_qty' => 'required|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    public function render()
    {
        $query = StockOpname::with(['warehouse', 'product', 'creator', 'approver']);

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        if ($this->warehouseFilter !== 'all') {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $stockOpnames = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $warehousesList = Warehouse::where('is_active', true)->get();
        $productsList = Product::all();

        return view('livewire.stock-opname.stock-opname-list', [
            'stockOpnames' => $stockOpnames,
            'warehousesList' => $warehousesList,
            'productsList' => $productsList
        ])->title('Stock Opname - Inventory Management');
    }

    public function resetForm()
    {
        $this->reset([
            'opnameId', 'warehouse_id', 'product_id', 'system_qty',
            'actual_qty', 'notes'
        ]);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function updatedProductId()
    {
        if ($this->product_id && $this->warehouse_id) {
            $stock = Stock::where('product_id', $this->product_id)
                ->where('warehouse_id', $this->warehouse_id)
                ->first();
            // FIXED: Use qty_on_hand instead of quantity
            $this->system_qty = $stock ? $stock->qty_on_hand : 0;
        }
    }

    public function updatedWarehouseId()
    {
        if ($this->product_id && $this->warehouse_id) {
            $stock = Stock::where('product_id', $this->product_id)
                ->where('warehouse_id', $this->warehouse_id)
                ->first();
            // FIXED: Use qty_on_hand instead of quantity
            $this->system_qty = $stock ? $stock->qty_on_hand : 0;
        }
    }

    public function save()
    {
        $this->validate();

        $stock = Stock::where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();
        // FIXED: Use qty_on_hand instead of quantity
        $systemQty = $stock ? $stock->qty_on_hand : 0;

        $data = [
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'system_qty' => $systemQty,
            'actual_qty' => $this->actual_qty,
            'variance_qty' => $this->actual_qty - $systemQty,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
            'status' => 'pending',
        ];

        if ($this->opnameId) {
            $opname = StockOpname::find($this->opnameId);
            $opname->update($data);
            $this->dispatch('toast', ['message' => 'Stock opname updated successfully', 'type' => 'success']);
        } else {
            StockOpname::create($data);
            $this->dispatch('toast', ['message' => 'Stock opname created successfully', 'type' => 'success']);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Show adjustment confirmation modal.
     */
    public function showAdjust($id)
    {
        $opname = StockOpname::find($id);
        if ($opname && $opname->status === 'completed') {
            // Check if user can approve
            if (!auth()->user()->can('stock-opname.approve')) {
                $this->dispatch('toast', ['message' => 'You do not have permission to approve stock adjustments', 'type' => 'error']);
                return;
            }
            $this->opnameId = $id;
            $this->showAdjustModal = true;
        }
    }

    /**
     * Apply stock adjustment with approval and create movement record.
     */
    public function applyAdjustment()
    {
        // Check permission
        if (!auth()->user()->can('stock-opname.approve')) {
            $this->dispatch('toast', ['message' => 'You do not have permission to approve stock adjustments', 'type' => 'error']);
            return;
        }

        try {
            DB::transaction(function () {
                // Lock the opname record to prevent race conditions (fetch INSIDE transaction)
                $opname = StockOpname::lockForUpdate()->find($this->opnameId);

                if (!$opname || $opname->status !== 'completed') {
                    throw new \InvalidArgumentException('Invalid opname status or opname not found');
                }

                // Lock the stock record
                $stock = Stock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $opname->product_id,
                        'warehouse_id' => $opname->warehouse_id
                    ],
                    ['qty_on_hand' => 0, 'avg_cost' => 0, 'qty_reserved' => 0]
                );

                $qtyBefore = $stock->qty_on_hand;
                $qtyAfter = $opname->actual_qty;
                $variance = $qtyAfter - $qtyBefore;

                // Prevent negative stock
                if ($qtyAfter < 0) {
                    throw new \InvalidArgumentException('Cannot adjust to negative stock quantity');
                }

                // Prevent adjusting below reserved quantity
                if ($qtyAfter < $stock->qty_reserved) {
                    throw new \InvalidArgumentException(
                        sprintf('Cannot adjust below reserved quantity. Reserved: %d, Requested: %d', $stock->qty_reserved, $qtyAfter)
                    );
                }

                // Update stock
                $stock->update([
                    'qty_on_hand' => $qtyAfter,
                    'last_movement_at' => now(),
                ]);

                // Create stock movement record for audit trail
                StockMovement::create([
                    'product_id' => $opname->product_id,
                    'warehouse_id' => $opname->warehouse_id,
                    'type' => 'adjust',
                    'qty' => $variance,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'notes' => "Stock opname adjustment: {$opname->notes}",
                    'reference_type' => StockOpname::class,
                    'reference_id' => $opname->id,
                    'created_by' => Auth::id(),
                    'moved_at' => now(),
                ]);

                // Update opname status
                $opname->update([
                    'status' => 'adjusted',
                    'adjusted_at' => now(),
                    'adjusted_by' => Auth::id(),
                ]);

                return $opname;
            });

            $this->dispatch('toast', ['message' => 'Stock adjusted successfully', 'type' => 'success']);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', ['message' => $e->getMessage(), 'type' => 'error']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'Failed to adjust stock: ' . $e->getMessage(), 'type' => 'error']);
        }

        $this->showAdjustModal = false;
        $this->opnameId = null;
    }

    /**
     * Legacy method - kept for backwards compatibility.
     * @deprecated Use showAdjust() and applyAdjustment() instead.
     */
    public function adjustStock($id)
    {
        $this->showAdjust($id);
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

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function view($id)
    {
        $this->opnameId = $id;
        $this->showViewModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $opname = StockOpname::findOrFail($id);

        // Only allow editing pending records
        if ($opname->status !== 'pending') {
            $this->dispatch('toast', ['message' => 'Only pending stock opnames can be edited', 'type' => 'warning']);
            return;
        }

        $this->opnameId = $opname->id;
        $this->warehouse_id = $opname->warehouse_id;
        $this->product_id = $opname->product_id;
        $this->system_qty = $opname->system_qty;
        $this->actual_qty = $opname->actual_qty;
        $this->notes = $opname->notes;

        $this->showModal = true;
    }

    public function confirmComplete($id)
    {
        $opname = StockOpname::find($id);
        if ($opname && $opname->status === 'pending') {
            $this->opnameId = $id;
            $this->showCompleteModal = true;
        }
    }

    public function complete()
    {
        $opname = StockOpname::find($this->opnameId);
        if ($opname && $opname->status === 'pending') {
            $opname->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
            ]);
            $this->dispatch('toast', ['message' => 'Stock opname completed successfully', 'type' => 'success']);
        }
        $this->showCompleteModal = false;
        $this->opnameId = null;
    }

    public function confirmDelete($id)
    {
        $opname = StockOpname::find($id);
        if ($opname && $opname->status === 'pending') {
            $this->opnameId = $id;
            $this->showDeleteModal = true;
        } else {
            $this->dispatch('toast', ['message' => 'Only pending stock opnames can be deleted', 'type' => 'warning']);
        }
    }

    public function delete()
    {
        $opname = StockOpname::find($this->opnameId);
        if ($opname && $opname->status === 'pending') {
            $opname->delete();
            $this->dispatch('toast', ['message' => 'Stock opname deleted successfully', 'type' => 'success']);
        }
        $this->showDeleteModal = false;
        $this->opnameId = null;
    }

    public function cancel($id)
    {
        $opname = StockOpname::find($id);
        if ($opname && in_array($opname->status, ['pending', 'completed'])) {
            $opname->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
            ]);
            $this->dispatch('toast', ['message' => 'Stock opname cancelled', 'type' => 'warning']);
        }
    }
}
