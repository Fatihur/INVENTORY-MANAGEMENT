@php
$reportData = $this->getReportData();
@endphp

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
    <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['total_low_stock'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Low Stock Products</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">Rp {{ number_format($reportData['total_shortage_value'], 0, ',', '.') }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Shortage Value</div>
        </div>
    </div>
</div>

<!-- Low Stock Table -->
<div style="overflow-x: auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th class="text-right">Min Stock</th>
                <th class="text-right">Current</th>
                <th class="text-right">Shortage</th>
                <th>Supplier</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['products'] as $product)
            <tr style="background: {{ $product->current_stock == 0 ? '#ffebee' : '#fff8e1' }}">
                <td><strong style="color: #3498db;">{{ $product->sku }}</strong></td>
                <td>{{ $product->name }}</td>
                <td class="text-right"><span class="badge badge-warning">{{ $product->min_stock }}</span></td>
                <td class="text-right">
                    <strong style="color: {{ $product->current_stock == 0 ? '#e74c3c' : '#f39c12' }}">
                        {{ $product->current_stock }}
                    </strong>
                </td>
                <td class="text-right"><span class="badge badge-danger">{{ $product->shortage }}</span></td>
                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">Create PO</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; color: #27ae60;"><i class="fas fa-check-circle"></i></div>
                    <p>No low stock products. All inventory levels are healthy!</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
