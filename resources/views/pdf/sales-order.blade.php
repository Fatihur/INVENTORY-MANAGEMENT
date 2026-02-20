<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Order - {{ $salesOrder->so_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .order-info { margin-bottom: 20px; }
        .info-row { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .totals { float: right; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 10px; }
        .totals-row.total { font-weight: bold; background-color: #f5f5f5; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 200px; padding-top: 60px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName }}</div>
        <div>Sales Order</div>
    </div>

    <div class="order-info">
        <div class="info-row"><strong>Order Number:</strong> {{ $salesOrder->so_number }}</div>
        <div class="info-row"><strong>Order Date:</strong> {{ $salesOrder->order_date->format('d M Y') }}</div>
        <div class="info-row"><strong>Delivery Date:</strong> {{ $salesOrder->delivery_date?->format('d M Y') ?? 'N/A' }}</div>
        <div class="info-row"><strong>Customer:</strong> {{ $salesOrder->customer->name }}</div>
        <div class="info-row"><strong>Email:</strong> {{ $salesOrder->customer->email ?? 'N/A' }}</div>
        <div class="info-row"><strong>Phone:</strong> {{ $salesOrder->customer->phone ?? 'N/A' }}</div>
        @if($salesOrder->warehouse)
        <div class="info-row"><strong>Warehouse:</strong> {{ $salesOrder->warehouse->name }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Product</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Tax</th>
                <th style="width: 15%;">Discount</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td>{{ $item->tax_rate }}%</td>
                <td>{{ $item->discount_percent }}%</td>
                <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row">
            <span>Tax:</span>
            <span>Rp {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row">
            <span>Discount:</span>
            <span>Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row total">
            <span>Total:</span>
            <span>Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    @if($salesOrder->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $salesOrder->notes }}</p>
    </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            Ordered By
        </div>
        <div class="signature-box">
            Approved By
        </div>
    </div>

    <div class="footer">
        Generated on {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</body>
</html>
