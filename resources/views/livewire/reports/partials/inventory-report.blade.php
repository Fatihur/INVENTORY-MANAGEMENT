@php
$reportData = $this->getReportData();
@endphp

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
    <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['total_products'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Products</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['total_quantity'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Quantity</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">Rp {{ number_format($reportData['total_value'], 0, ',', '.') }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Value</div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div style="overflow-x: auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Stock Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['products'] as $product)
            <tr>
                <td><strong style="color: #3498db;">{{ $product->sku }}</strong></td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category ?? '-' }}</td>
                <td class="text-right"><strong>{{ $product->stock_qty }}</strong></td>
                <td class="text-right">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($product->stock_value, 0, ',', '.') }}</strong></td>
                <td>
                    @if($product->stock_qty == 0)
                        <span class="badge badge-danger">Out of Stock</span>
                    @elseif($product->stock_qty <= $product->min_stock)
                        <span class="badge badge-warning">Low Stock</span>
                    @else
                        <span class="badge badge-success">OK</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; color: #bdc3c7;"><i class="fas fa-box"></i></div>
                    <p>No products found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8f9fa; font-weight: bold;">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">-</td>
                <td class="text-right">Rp {{ number_format($reportData['total_value'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
