@php
$reportData = $this->getReportData();
@endphp

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
    <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['expired_count'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Expired</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['expiring_30_count'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Expiring (30 days)</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['expiring_60_count'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Expiring (60 days)</div>
        </div>
    </div>
    <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['expiring_90_count'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Expiring (90 days)</div>
        </div>
    </div>
</div>

<!-- Expired Batches -->
@if(count($reportData['expired']) > 0)
<div class="panel" style="margin-bottom: 20px; border: 2px solid #e74c3c;">
    <div class="panel-header" style="background: #e74c3c; color: white;"><i class="fas fa-exclamation-triangle"></i> Expired Batches - Immediate Action Required</div>
    <div class="panel-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Batch #</th>
                    <th>Product</th>
                    <th>Expiry Date</th>
                    <th class="text-right">Remaining Qty</th>
                    <th>Days Overdue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['expired'] as $batch)
                <tr style="background: #ffebee;">
                    <td><strong>{{ $batch->batch_number }}</strong></td>
                    <td>{{ $batch->product?->name ?? 'N/A' }}</td>
                    <td style="color: #e74c3c; font-weight: bold;">{{ $batch->expiry_date->format('d M Y') }}</td>
                    <td class="text-right">{{ $batch->remaining_qty }}</td>
                    <td><span class="badge badge-danger">{{ $batch->expiry_date->diffInDays(now()) }} days</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Expiring Soon Batches -->
@if(count($reportData['expiring_30']) > 0)
<div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header"><i class="fas fa-clock"></i> Expiring Within 30 Days</div>
    <div class="panel-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Batch #</th>
                    <th>Product</th>
                    <th>Expiry Date</th>
                    <th class="text-right">Remaining Qty</th>
                    <th>Days Left</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['expiring_30'] as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_number }}</strong></td>
                    <td>{{ $batch->product?->name ?? 'N/A' }}</td>
                    <td style="color: #f39c12; font-weight: bold;">{{ $batch->expiry_date->format('d M Y') }}</td>
                    <td class="text-right">{{ $batch->remaining_qty }}</td>
                    <td><span class="badge badge-warning">{{ $batch->expiry_date->diffInDays(now()) }} days</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(count($reportData['expired']) == 0 && count($reportData['expiring_30']) == 0)
<div style="text-align: center; padding: 60px; background: #e8f5e9; border-radius: 8px;">
    <div style="font-size: 64px; margin-bottom: 20px; color: #27ae60;"><i class="fas fa-check-circle"></i></div>
    <h3 style="color: #27ae60; margin-bottom: 10px;">All Clear!</h3>
    <p style="color: #7f8c8d;">No expired or expiring batches found. Your inventory is healthy.</p>
</div>
@endif
