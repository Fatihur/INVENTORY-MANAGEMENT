<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName }}</div>
        <div>Purchase Order</div>
    </div>

    <div class="order-info">
        <div class="info-row"><strong>Order Number:</strong> {{ $purchaseOrder->po_number }}</div>
        <div class="info-row"><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('d M Y') }}</div>
        <div class="info-row"><strong>Expected Date:</strong> {{ $purchaseOrder->expected_date?->format('d M Y') ?? 'N/A' }}</div>
        <div class="info-row"><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</div>
        <div class="info-row"><strong>Contact:</strong> {{ $purchaseOrder->supplier->contact_person ?? 'N/A' }}</div>
        <div class="info-row"><strong>Phone:</strong> {{ $purchaseOrder->supplier->phone ?? 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Product</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row">
            <span>Tax:</span>
            <span>Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</span>
        </div>
        <div class="totals-row total">
            <span>Total:</span>
            <span>Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    @if($purchaseOrder->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $purchaseOrder->notes }}</p>
    </div>
    @endif

    <div class="footer">
        Generated on {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</body>
</html>
