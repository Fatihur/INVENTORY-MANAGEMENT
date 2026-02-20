<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Labels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            margin: 0 auto;
        }
        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm;
        }
        .label {
            border: 1px solid #000;
            padding: 5mm;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-qr {
            margin-bottom: 2mm;
        }
        .label-qr svg {
            width: 25mm;
            height: 25mm;
        }
        .label-sku {
            font-weight: bold;
            font-size: 9px;
        }
        .label-name {
            font-size: 8px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .label-code {
            font-size: 7px;
            color: #999;
            margin-top: 1mm;
        }
        @media print {
            .page {
                width: 100%;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="no-print" style="margin-bottom: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">Print Labels</button>
        </div>

        <div class="label-grid">
            @foreach($qrs as $qr)
            <div class="label">
                <div class="label-qr">
                    {!! app(\App\Contracts\Services\QrCodeServiceInterface::class)->getSvg($qr->qr_code_value) !!}
                </div>
                <div class="label-sku">{{ $qr->product->sku ?? 'N/A' }}</div>
                <div class="label-name">{{ $qr->product->name ?? 'Unknown' }}</div>
                <div class="label-code">{{ $qr->qr_code_value }}</div>
                @if($qr->batch_number)
                    <div class="label-code">Batch: {{ $qr->batch_number }}</div>
                @endif
                @if($qr->expiry_date)
                    <div class="label-code">Exp: {{ $qr->expiry_date->format('Y-m-d') }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>
