<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .filters { margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; font-size: 10px; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
        .low-stock { background-color: #fee2e2; }
        .out-of-stock { background-color: #fecaca; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName ?? 'Inventory Management' }}</div>
        <div>Inventory Report</div>
    </div>

    @if(isset($filters) && $filters)
    <div class="filters">
        <strong>Filters:</strong>
        @if(isset($filters['category']))
        <span>Category: {{ $filters['category'] }}</span>
        @endif
        @if(isset($filters['warehouse']))
        <span>Warehouse: {{ $filters['warehouse'] }}</span>
        @endif
        <span>Generated: {{ $generatedAt->format('d M Y H:i:s') }}</span>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Code</th>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 10%;" class="text-right">Qty</th>
                <th style="width: 10%;" class="text-right">Min Stock</th>
                <th style="width: 10%;" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($products ?? [] as $product)
            @php
                $totalQty = $product->stocks->sum('qty') ?? 0;
                $minStock = $product->min_stock ?? 0;
                $statusClass = '';
                $statusText = 'OK';
                if ($totalQty == 0) {
                    $statusClass = 'out-of-stock';
                    $statusText = 'Out of Stock';
                } elseif ($totalQty <= $minStock) {
                    $statusClass = 'low-stock';
                    $statusText = 'Low Stock';
                }
            @endphp
            <tr class="{{ $statusClass }}">
                <td>{{ $no++ }}</td>
                <td>{{ $product->code }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>{{ $product->unit }}</td>
                <td class="text-right">{{ $totalQty }}</td>
                <td class="text-right">{{ $minStock }}</td>
                <td class="text-right">{{ $statusText }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">No products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</body>
</html>
