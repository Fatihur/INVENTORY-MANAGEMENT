@php
$reportData = $this->getReportData();
@endphp

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
    <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">Rp {{ number_format($reportData['total_sales'], 0, ',', '.') }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Sales</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['total_orders'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Orders</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">Rp {{ number_format($reportData['average_order'], 0, ',', '.') }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Average Order</div>
        </div>
    </div>
</div>

<!-- Sales by Status -->
@if(count($reportData['sales_by_status']) > 0)
<div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header">Sales by Status</div>
    <div class="panel-body">
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            @foreach($reportData['sales_by_status'] as $status => $data)
            <div style="flex: 1; min-width: 150px; background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase;">{{ ucfirst($status) }}</div>
                <div style="font-size: 20px; font-weight: bold; color: #2c3e50;">{{ $data['count'] }}</div>
                <div style="font-size: 12px; color: #27ae60;">Rp {{ number_format($data['total'], 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Orders Table -->
<div style="overflow-x: auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="text-right">Items</th>
                <th class="text-right">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['orders'] as $order)
            @php
                $statusConfig = [
                    'draft' => 'badge-warning',
                    'confirmed' => 'badge-info',
                    'processing' => 'badge-warning',
                    'shipped' => 'badge-info',
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                ];
            @endphp
            <tr>
                <td><strong style="color: #3498db;">{{ $order->so_number }}</strong></td>
                <td>{{ $order->order_date->format('d M Y') }}</td>
                <td>{{ $order->customer?->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $order->items->count() }}</td>
                <td class="text-right"><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                <td><span class="badge {{ $statusConfig[$order->status] ?? 'badge-default' }}">{{ ucfirst($order->status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; color: #bdc3c7;"><i class="fas fa-shopping-cart"></i></div>
                    <p>No sales orders found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
