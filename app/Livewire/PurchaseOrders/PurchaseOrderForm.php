<?php

namespace App\Livewire\PurchaseOrders;

use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    public ?PurchaseOrder $purchaseOrder = null;

    public ?int $supplier_id = null;
    public string $status = 'draft';
    public string $order_date = '';
    public ?string $expected_delivery_date = null;
    public string $notes = '';
    public array $items = [];

    protected function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'items.required' => 'Please add at least one item',
        'items.*.product_id.required' => 'Please select a product',
        'items.*.qty_ordered.min' => 'Quantity must be at least 1',
        'items.*.unit_price.min' => 'Price cannot be negative',
    ];

    public function mount(?int $purchaseOrder = null)
    {
        $this->order_date = now()->format('Y-m-d');

        if ($purchaseOrder) {
            $this->purchaseOrder = PurchaseOrder::with('items.product')->findOrFail($purchaseOrder);
            $this->supplier_id = $this->purchaseOrder->supplier_id;
            $this->order_date = $this->purchaseOrder->order_date->format('Y-m-d');
            $this->expected_delivery_date = $this->purchaseOrder->expected_delivery_date?->format('Y-m-d');
            $this->notes = $this->purchaseOrder->notes ?? '';

            // Load existing items
            $this->items = $this->purchaseOrder->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'qty_ordered' => $item->qty_ordered,
                'unit_price' => $item->unit_price,
            ])->toArray();
        }

        // Add first item if empty
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => null,
            'qty_ordered' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function updated($property)
    {
        // Auto-fill unit price when product is selected
        if (str_starts_with($property, 'items.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3 && $parts[2] === 'product_id') {
                $index = $parts[1];
                $productId = $this->items[$index]['product_id'] ?? null;

                if ($productId && $this->supplier_id) {
                    $supplierProduct = \DB::table('supplier_products')
                        ->where('supplier_id', $this->supplier_id)
                        ->where('product_id', $productId)
                        ->first();

                    if ($supplierProduct) {
                        $this->items[$index]['unit_price'] = $supplierProduct->buy_price ?? 0;
                    }
                }
            }
        }
    }

    public function calculateTotals(): array
    {
        $subtotal = 0;

        foreach ($this->items as $item) {
            $qty = $item['qty_ordered'] ?? 0;
            $price = $item['unit_price'] ?? 0;
            $subtotal += $qty * $price;
        }

        // Use configurable tax rate (consistent with PurchaseOrderService)
        $taxRate = config('inventory.tax_rate', 0.11);
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }

    public function save(PurchaseOrderServiceInterface $poService)
    {
        $this->validate();

        $totals = $this->calculateTotals();

        $data = [
            'supplier_id' => $this->supplier_id,
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'notes' => $this->notes,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax'],
            'total_amount' => $totals['total'],
        ];

        if ($this->purchaseOrder) {
            // Update existing PO
            $this->purchaseOrder->update($data);

            // Sync items - delete old, create new
            $this->purchaseOrder->items()->delete();
            foreach ($this->items as $item) {
                $itemTotal = $item['qty_ordered'] * $item['unit_price'];
                $this->purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $itemTotal,
                ]);
            }

            session()->flash('message', 'Purchase Order updated successfully.');
        } else {
            // Create new PO
            $data['created_by'] = auth()->id();
            $data['status'] = 'draft';

            $po = PurchaseOrder::create($data);

            foreach ($this->items as $item) {
                $itemTotal = $item['qty_ordered'] * $item['unit_price'];
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $itemTotal,
                ]);
            }

            session()->flash('message', 'Purchase Order created successfully.');
        }

        return redirect()->route('purchase-orders.index');
    }

    public function render()
    {
        $totals = $this->calculateTotals();

        return view('livewire.purchase-orders.purchase-order-form', [
            'suppliers' => Supplier::where('is_active', true)->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'total' => $totals['total'],
        ]);
    }
}
