<?php

namespace App\Livewire;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\SalesOrder\SalesOrderService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SalesOrders extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showModal = false;
    public $showViewModal = false;
    public $orderId = null;

    public $customer_id = '';
    public $warehouse_id = '';
    public $order_date = '';
    public $delivery_date = '';
    public $notes = '';
    public $items = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'order_date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:order_date',
        'items' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->order_date = date('Y-m-d');
    }

    public function render()
    {
        $query = SalesOrder::with(['customer', 'items.product']);

        if ($this->search) {
            $query->where('so_number', 'like', "%{$this->search}%");
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        $orders = $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.sales-orders.index', [
            'orders' => $orders
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'orderId', 'customer_id', 'warehouse_id', 'order_date',
            'delivery_date', 'notes', 'items'
        ]);
        $this->order_date = date('Y-m-d');
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->addItem();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $order = SalesOrder::with('items')->findOrFail($id);

        $this->orderId = $order->id;
        $this->customer_id = $order->customer_id;
        $this->warehouse_id = $order->warehouse_id;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->delivery_date = $order->delivery_date?->format('Y-m-d');
        $this->notes = $order->notes;
        $this->items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'discount_percent' => $item->discount_percent,
                'notes' => $item->notes,
            ];
        })->toArray();

        $this->showModal = true;
    }

    public function view($id)
    {
        $this->resetForm();
        $order = SalesOrder::with(['customer', 'customer.addresses', 'items.product', 'warehouse'])->findOrFail($id);

        $this->orderId = $order->id;
        $this->customer_id = $order->customer_id;
        $this->warehouse_id = $order->warehouse_id;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->delivery_date = $order->delivery_date?->format('Y-m-d');
        $this->notes = $order->notes;
        $this->items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'discount_percent' => $item->discount_percent,
                'notes' => $item->notes,
            ];
        })->toArray();

        $this->showViewModal = true;
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 0,
            'discount_percent' => 0,
            'notes' => '',
        ];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems()
    {
        $this->dispatch('items-updated');
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'customer_id' => $this->customer_id,
                'warehouse_id' => $this->warehouse_id ?: null,
                'status' => 'draft',
                'order_date' => $this->order_date,
                'delivery_date' => $this->delivery_date ?: null,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ];

            if ($this->orderId) {
                $order = SalesOrder::find($this->orderId);
                $order->update($data);
                $order->items()->delete();
            } else {
                $order = SalesOrder::create($data);
            }

            foreach ($this->items as $item) {
                if (!empty($item['product_id'])) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            $order->calculateTotals();

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('order-saved');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Failed to save order: ' . $e->getMessage());
        }
    }

    public function confirm($id)
    {
        $order = SalesOrder::find($id);
        if (!$order || $order->status !== 'draft') {
            $this->dispatch('error', message: 'Order not found or cannot be confirmed');
            return;
        }

        try {
            $service = app(SalesOrderService::class);
            $service->confirmOrder($order);
            $this->dispatch('order-confirmed');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('error', message: $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to confirm order: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $order = SalesOrder::find($id);
        if (!$order) {
            $this->dispatch('error', message: 'Order not found');
            return;
        }

        try {
            $service = app(SalesOrderService::class);
            $service->cancelOrder($order);
            $this->dispatch('order-cancelled');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('error', message: $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $order = SalesOrder::find($id);
        if ($order && in_array($order->status, ['draft', 'cancelled'])) {
            $order->delete();
            $this->dispatch('order-deleted');
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
}
