<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e74c3c; }
        .header h1 { font-size: 18px; color: #2c3e50; }
        .header p { color: #7f8c8d; font-size: 10px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .summary-box { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; width: 48%; }
        .summary-box .number { font-size: 16px; font-weight: bold; color: #e74c3c; }
        .summary-box .label { font-size: 9px; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e74c3c; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ecf0f1; font-size: 10px; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr.out-of-stock { background: #ffebee; }
        tr.low-stock { background: #fff8e1; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; color: white; }
        .badge-danger { background: #e74c3c; }
        .badge-warning { background: #f39c12; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Low Stock Alert Report</h1>
        <p>Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="number">{{ $data['total_low_stock'] }}</div>
            <div class="label">Low Stock Products</div>
        </div>
        <div class="summary-box">
            <div class="number">Rp {{ number_format($data['total_shortage_value'], 0, ',', '.') }}</div>
            <div class="label">Shortage Value</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th class="text-right">Min Stock</th>
                <th class="text-right">Current</th>
                <th class="text-right">Shortage</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['products'] as $product)
            <tr class="{{ $product->current_stock == 0 ? 'out-of-stock' : 'low-stock' }}">
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-right">{{ $product->min_stock }}</td>
                <td class="text-right" style="font-weight: bold; color: {{ $product->current_stock == 0 ? '#e74c3c' : '#f39c12' }}">
                    {{ $product->current_stock }}
                </td>
                <td class="text-right">{{ $product->shortage }}</td>
                <td>{{ $product->suppliers->first()?->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Inventory System</p>
    </div>
</body>
</html>
