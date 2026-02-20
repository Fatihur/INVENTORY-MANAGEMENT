<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #27ae60; }
        .header h1 { font-size: 18px; color: #2c3e50; }
        .header p { color: #7f8c8d; font-size: 10px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .summary-box { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; width: 30%; }
        .summary-box .number { font-size: 16px; font-weight: bold; color: #27ae60; }
        .summary-box .label { font-size: 9px; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #27ae60; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ecf0f1; font-size: 10px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; color: white; }
        .badge-draft { background: #95a5a6; }
        .badge-confirmed { background: #3498db; }
        .badge-processing { background: #f39c12; }
        .badge-shipped { background: #9b59b6; }
        .badge-completed { background: #27ae60; }
        .badge-cancelled { background: #e74c3c; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <p>Period: {{ $dateFrom }} - {{ $dateTo }} | Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="number">Rp {{ number_format($data['total_sales'], 0, ',', '.') }}</div>
            <div class="label">Total Sales</div>
        </div>
        <div class="summary-box">
            <div class="number">{{ $data['total_orders'] }}</div>
            <div class="label">Total Orders</div>
        </div>
        <div class="summary-box">
            <div class="number">Rp {{ number_format($data['average_order'], 0, ',', '.') }}</div>
            <div class="label">Average Order</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th class="text-right">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['orders'] as $order)
            <tr>
                <td>{{ $order->so_number }}</td>
                <td>{{ $order->customer?->name ?? 'N/A' }}</td>
                <td>{{ $order->order_date?->format('d M Y') }}</td>
                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                <td><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Inventory System</p>
    </div>
</body>
</html>
