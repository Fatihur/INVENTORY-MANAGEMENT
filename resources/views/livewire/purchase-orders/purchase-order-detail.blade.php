<div>
    @if(session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb;">{{ session('message') }}</div>
    @endif
    @if(session()->has('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb;">{{ session('error') }}</div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
        <div>
            <h2 style="font-size: 14px; font-weight: bold;">{{ $purchaseOrder->po_number }}</h2>
            <p style="color: #7f8c8d; font-size: 11px;">Supplier: {{ $purchaseOrder->supplier->name }}</p>
        </div>
        <div style="display: flex; gap: 10px;">
            @php
                $statusColors = [
                    'draft' => 'badge-default',
                    'sent' => 'badge-info',
                    'approved' => 'badge-success',
                    'partial' => 'badge-warning',
                    'received' => 'badge-success',
                    'cancelled' => 'badge-danger',
                ];
                $color = $statusColors[$purchaseOrder->status->value] ?? 'badge-default';
            @endphp
            <span class="badge {{ $color }}" style="font-size: 12px; padding: 5px 10px;">{{ $purchaseOrder->status->label() }}</span>

            @if($purchaseOrder->status->value === 'draft')
                <button wire:click="sendForApproval" class="btn btn-success" style="background-color: #27ae60; color: #fff;">Send for Approval</button>
            @endif
            @if($purchaseOrder->canApprove())
                <button wire:click="approve" class="btn btn-success" style="background-color: #27ae60; color: #fff;">Approve</button>
            @endif
            @if($purchaseOrder->canReceive())
                <a wire:navigate href="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="btn btn-primary">Receive Goods</a>
            @endif
            <a wire:navigate href="{{ route('purchase-orders.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div>
                    <p style="font-size: 11px; color: #7f8c8d;">Order Date</p>
                    <p style="font-weight: bold;">{{ $purchaseOrder->order_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <p style="font-size: 11px; color: #7f8c8d;">Expected Delivery</p>
                    <p style="font-weight: bold;">{{ $purchaseOrder->expected_delivery_date?->format('Y-m-d') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p style="font-size: 11px; color: #7f8c8d;">Subtotal</p>
                    <p style="font-weight: bold;">Rp {{ number_format($purchaseOrder->subtotal, 0) }}</p>
                </div>
                <div>
                    <p style="font-size: 11px; color: #7f8c8d;">Total</p>
                    <p style="font-weight: bold; font-size: 14px;">Rp {{ number_format($purchaseOrder->total_amount, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">PO Items</div>
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Ordered</th>
                        <th>Received</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty_ordered }}</td>
                        <td>{{ $item->qty_received }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0) }}</td>
                        <td>Rp {{ number_format($item->total_price, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
