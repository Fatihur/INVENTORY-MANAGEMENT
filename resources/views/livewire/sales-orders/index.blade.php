<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Sales Orders</h2>
        @can('sales-orders.create')
        <button wire:click="create" class="btn btn-primary">+ New Order</button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; border-bottom: 1px solid #bdc3c7;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Order number..." class="form-control" style="width: 200px;">
            <select wire:model.live="status" class="form-control" style="width: 150px;">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select wire:model.live="perPage" class="form-control" style="width: 120px;">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th class="text-right">Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <div style="font-weight: bold; color: #3498db;">{{ $order->so_number }}</div>
                            @if($order->notes)
                            <div style="color: #7f8c8d; font-size: 10px; margin-top: 2px;">Has notes</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $order->customer->name }}</div>
                            @if($order->customer->email)
                            <div style="color: #7f8c8d; font-size: 10px;">{{ $order->customer->email }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $order->order_date->format('d M Y') }}</div>
                            @if($order->delivery_date)
                            <div style="color: #7f8c8d; font-size: 10px;">Delivery: {{ $order->delivery_date->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <div style="font-weight: bold; color: #3498db;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            @if($order->discount_amount > 0)
                            <div style="color: #27ae60; font-size: 10px;">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }} discount</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'draft' => ['class' => 'badge-warning', 'label' => 'Draft'],
                                    'confirmed' => ['class' => 'badge-info', 'label' => 'Confirmed'],
                                    'processing' => ['class' => 'badge-warning', 'label' => 'Processing'],
                                    'shipped' => ['class' => 'badge-info', 'label' => 'Shipped'],
                                    'completed' => ['class' => 'badge-success', 'label' => 'Completed'],
                                    'cancelled' => ['class' => 'badge-danger', 'label' => 'Cancelled'],
                                ];
                                $config = $statusConfig[$order->status] ?? $statusConfig['draft'];
                            @endphp
                            <span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
                        </td>
                        <td>
                            <a href="#" wire:click.prevent="view({{ $order->id }})" style="color: #3498db;">View</a>
                            @can('sales-orders.edit')
                                @if(in_array($order->status, ['draft', 'pending']))
                                <a href="#" wire:click.prevent="edit({{ $order->id }})" style="color: #27ae60; margin-left: 10px;">Edit</a>
                                @endif
                                @if($order->status === 'draft')
                                <a href="#" wire:click.prevent="confirm({{ $order->id }})" style="color: #27ae60; margin-left: 10px;">Confirm</a>
                                @endif
                            @endcan
                            @can('sales-orders.delete')
                                @if(in_array($order->status, ['draft', 'confirmed', 'pending']))
                                <a href="#" wire:click.prevent="cancel({{ $order->id }})" style="color: #e74c3c; margin-left: 10px;"
                                    onclick="confirm('Are you sure you want to cancel this order?') || event.stopImmediatePropagation()">Cancel</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d;">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $orders->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $orders->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $orders->total() }}</strong> results
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showModal', false)">
        <div style="background: #fff; width: 800px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $orderId ? 'Edit Order' : 'New Sales Order' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 15px;">
                    <!-- Order Info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ecf0f1;">
                        <div>
                            <label class="form-label">Customer <span style="color: #e74c3c;">*</span></label>
                            <select wire:model="customer_id" class="form-control">
                                <option value="">-- Select Customer --</option>
                                @foreach(\App\Models\Customer::all() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Warehouse</label>
                            <select wire:model="warehouse_id" class="form-control">
                                <option value="">-- Select Warehouse --</option>
                                @foreach(\App\Models\Warehouse::all() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Order Date <span style="color: #e74c3c;">*</span></label>
                            <input type="date" wire:model="order_date" class="form-control">
                            @error('order_date') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Delivery Date</label>
                            <input type="date" wire:model="delivery_date" class="form-control">
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div style="margin-bottom: 15px;">
                        <h4 style="font-size: 13px; font-weight: bold; margin-bottom: 10px;">Order Items</h4>
                        @foreach($items as $index => $item)
                        <div style="display: grid; grid-template-columns: 4fr 1fr 2fr 1fr 1fr 1fr; gap: 5px; margin-bottom: 8px; padding: 8px; background: #f8f9fa; border: 1px solid #ecf0f1;">
                            <div>
                                <label class="form-label" style="font-size: 10px;">Product</label>
                                <select wire:model="items.{{ $index }}.product_id" class="form-control">
                                    <option value="">-- Select --</option>
                                    @foreach(\App\Models\Product::all() as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 10px;">Qty</label>
                                <input type="number" wire:model="items.{{ $index }}.quantity" class="form-control">
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 10px;">Unit Price</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price" class="form-control">
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 10px;">Tax %</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.tax_rate" class="form-control">
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 10px;">Disc %</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.discount_percent" class="form-control">
                            </div>
                            <div style="display: flex; align-items: flex-end;">
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-danger" style="width: 100%;"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        @endforeach
                        <button type="button" wire:click="addItem" class="btn btn-default" style="margin-top: 5px;">+ Add Item</button>
                    </div>

                    <div>
                        <label class="form-label">Order Notes</label>
                        <textarea wire:model="notes" rows="2" placeholder="Additional notes or instructions..." class="form-control"></textarea>
                    </div>
                </div>

                <div style="padding: 10px 15px; border-top: 1px solid #bdc3c7; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $orderId ? 'Update Order' : 'Create Order' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showViewModal', false)">
        <div style="background: #fff; width: 700px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7;">
            <div class="panel-header">Order Details</div>

            <div style="padding: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ecf0f1;">
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Order Number</div>
                        <div style="font-weight: bold; color: #3498db;">{{ \App\Models\SalesOrder::find($orderId)?->so_number ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Customer</div>
                        <div style="font-weight: bold;">{{ \App\Models\Customer::find($customer_id)?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Order Date</div>
                        <div style="font-weight: bold;">{{ $order_date }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Status</div>
                        <span class="badge badge-info">{{ \App\Models\SalesOrder::find($orderId)?->status ?? 'draft' }}</span>
                    </div>
                </div>

                <!-- Order Items -->
                <h4 style="font-size: 13px; font-weight: bold; margin-bottom: 10px;">Order Items</h4>
                <table class="data-table" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @if($item['product_id'])
                        <tr>
                            <td>{{ \App\Models\Product::find($item['product_id'])?->name ?? '-' }}</td>
                            <td class="text-right">{{ $item['quantity'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                            <td class="text-right" style="font-weight: bold;">Rp {{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 10px 15px; border-top: 1px solid #bdc3c7; display: flex; justify-content: flex-end; gap: 10px;">
                @if(in_array(\App\Models\SalesOrder::find($orderId)?->status ?? '', ['draft', 'pending']))
                <button wire:click="edit({{ $orderId }})" class="btn btn-success">Edit</button>
                @endif
                <button wire:click="$set('showViewModal', false)" class="btn btn-default">Close</button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        $wire.on('order-saved', () => $wire.$refresh());
        $wire.on('order-confirmed', () => $wire.$refresh());
        $wire.on('order-cancelled', () => $wire.$refresh());
        $wire.on('order-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
