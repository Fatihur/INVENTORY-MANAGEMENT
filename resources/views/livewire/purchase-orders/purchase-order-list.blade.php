<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Purchase Orders</h2>
        @can('purchase-orders.create')
            <a wire:navigate href="{{ route('purchase-orders.create') }}" class="btn btn-primary">+ Create PO</a>
        @endcan
    </div>

    <div class="panel">
        <div class="panel-body" style="border-bottom: 1px solid #bdc3c7;">
            <select wire:model.live="status" class="form-control" style="width: 150px;">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
                <option value="partial">Partial</option>
                <option value="received">Received</option>
            </select>
        </div>

        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->po_number }}</td>
                        <td>{{ $po->supplier->name }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'badge-default',
                                    'sent' => 'badge-info',
                                    'approved' => 'badge-success',
                                    'partial' => 'badge-warning',
                                    'received' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                ];
                                $color = $statusColors[$po->status] ?? 'badge-default';
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'sent' => 'Sent',
                                    'approved' => 'Approved',
                                    'partial' => 'Partial',
                                    'received' => 'Received',
                                    'cancelled' => 'Cancelled',
                                ];
                            @endphp
                            <span class="badge {{ $color }}">{{ $statusLabels[$po->status] ?? ucfirst($po->status) }}</span>
                        </td>
                        <td>{{ $po->order_date->format('Y-m-d') }}</td>
                        <td>Rp {{ number_format($po->total_amount, 0) }}</td>
                        <td>
                            <a wire:navigate href="{{ route('purchase-orders.show', $po) }}" style="color: #3498db;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d;">No purchase orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">{{ $purchaseOrders->links() }}</div>
    </div>
</div>
