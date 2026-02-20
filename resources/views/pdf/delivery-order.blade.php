<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Order - {{ $salesOrder->so_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .order-info { margin-bottom: 20px; }
        .info-row { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 200px; padding-top: 60px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName }}</div>
        <div>Delivery Order</div>
    </div>

    <div class="order-info">
        <div class="info-row"><strong>Delivery Number:</strong> {{ $salesOrder->so_number }}</div>
        <div class="info-row"><strong>Order Date:</strong> {{ $salesOrder->order_date->format('d M Y') }}</div>
        <div class="info-row"><strong>Delivery Date:</strong> {{ $salesOrder->delivery_date?->format('d M Y') ?? 'N/A' }}</div>
        <div class="info-row"><strong>Customer:</strong> {{ $salesOrder->customer->name }}</div>
        <div class="info-row"><strong>Address:</strong> {{ $salesOrder->customer->addresses->first()?->address_line1 ?? 'N/A' }}</div>
        <div class="info-row"><strong>Phone:</strong> {{ $salesOrder->customer->phone ?? 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">Quantity Ordered</th>
                <th style="width: 15%;">Quantity Delivered</th>
                <th style="width: 15%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($salesOrder->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $salesOrder->notes }}</p>
    </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            Delivered By
        </div>
        <div class="signature-box">
            Received By
        </div>
    </div>

    <div class="footer">
        Generated on {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</body>
</html>
