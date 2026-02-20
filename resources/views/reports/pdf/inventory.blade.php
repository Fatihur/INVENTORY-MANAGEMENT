<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #3498db; }
        .header h1 { font-size: 18px; color: #2c3e50; }
        .header p { color: #7f8c8d; font-size: 10px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .summary-box { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; width: 30%; }
        .summary-box .number { font-size: 16px; font-weight: bold; color: #3498db; }
        .summary-box .label { font-size: 9px; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #3498db; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ecf0f1; font-size: 10px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-success { background: #27ae60; color: white; }
        .badge-warning { background: #f39c12; color: white; }
        .badge-danger { background: #e74c3c; color: white; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="number">{{ $data['total_products'] }}</div>
            <div class="label">Total Products</div>
        </div>
        <div class="summary-box">
            <div class="number">{{ $data['total_quantity'] }}</div>
            <div class="label">Total Quantity</div>
        </div>
        <div class="summary-box">
            <div class="number">Rp {{ number_format($data['total_value'], 0, ',', '.') }}</div>
            <div class="label">Total Value</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['products'] as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category ?? '-' }}</td>
                <td class="text-right">{{ $product->stock_qty }}</td>
                <td class="text-right">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($product->stock_value, 0, ',', '.') }}</td>
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
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Inventory System - Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>
</html>
