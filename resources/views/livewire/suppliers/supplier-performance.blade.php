<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
            <h2 style="font-size: 14px; font-weight: bold;">{{ $supplier->name }}</h2>
            <p style="color: #7f8c8d; font-size: 11px;">Code: {{ $supplier->code }}</p>
        </div>
        <a wire:navigate href="{{ route('suppliers.products', $supplier) }}" class="btn btn-primary">Manage Products</a>
        <a wire:navigate href="{{ route('suppliers.index') }}" class="btn btn-default">Back</a>
    </div>

    <!-- KPI Cards -->
    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
        <div style="flex: 1; background-color: #fff; border: 1px solid #bdc3c7; padding: 15px;">
            <div style="font-size: 11px; color: #7f8c8d;">On-Time Delivery Rate</div>
            <div style="font-size: 24px; font-weight: bold; color: {{ $onTimeRate >= 80 ? '#27ae60' : ($onTimeRate >= 50 ? '#f39c12' : '#e74c3c') }};">
                {{ number_format($onTimeRate, 1) }}%
            </div>
        </div>
        <div style="flex: 1; background-color: #fff; border: 1px solid #bdc3c7; padding: 15px;">
            <div style="font-size: 11px; color: #7f8c8d;">Average Lead Time</div>
            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">{{ $avgLeadTime ?? 'N/A' }} days</div>
        </div>
        <div style="flex: 1; background-color: #fff; border: 1px solid #bdc3c7; padding: 15px;">
            <div style="font-size: 11px; color: #7f8c8d;">Total Orders</div>
            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">{{ $supplier->purchase_orders_count }}</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">Recent Purchase Orders</div>
        <div class="panel-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->po_number }}</td>
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
                                $color = $statusColors[$po->status->value] ?? 'badge-default';
                            @endphp
                            <span class="badge {{ $color }}">{{ $po->status->label() }}</span>
                        </td>
                        <td>{{ $po->order_date->format('Y-m-d') }}</td>
                        <td>Rp {{ number_format($po->total_amount, 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #7f8c8d;">No purchase orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
