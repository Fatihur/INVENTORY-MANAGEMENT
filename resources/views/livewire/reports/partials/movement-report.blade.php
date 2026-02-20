@php
$reportData = $this->getReportData();
@endphp

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
    <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 24px; font-weight: bold;">{{ $reportData['total_movements'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">Total Movements</div>
        </div>
    </div>
    @foreach($reportData['movements_by_type'] as $type => $data)
    <div class="panel" style="background: linear-gradient(135deg, #{{ $type == 'in' ? '27ae60' : ($type == 'out' ? 'e74c3c' : 'f39c12') }}, #{{ $type == 'in' ? '219a52' : ($type == 'out' ? 'c0392b' : 'd68910') }}); color: white;">
        <div class="panel-body" style="padding: 15px;">
            <div style="font-size: 20px; font-weight: bold;">{{ $data['count'] }}</div>
            <div style="font-size: 12px; opacity: 0.9;">{{ ucfirst($type) }} ({{ $data['total_qty'] }} qty)</div>
        </div>
    </div>
    @endforeach
</div>

<!-- Movements Table -->
<div style="overflow-x: auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Type</th>
                <th>Product</th>
                <th>Warehouse</th>
                <th class="text-right">Quantity</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['movements'] as $movement)
            @php
                $typeColors = [
                    'in' => '#27ae60',
                    'out' => '#e74c3c',
                    'adjustment' => '#f39c12',
                    'transfer' => '#9b59b6',
                ];
            @endphp
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $movement->created_at->format('d M Y') }}</div>
                    <div style="font-size: 10px; color: #7f8c8d;">{{ $movement->created_at->format('H:i') }}</div>
                </td>
                <td><strong style="color: #3498db;">{{ $movement->reference_number ?? 'N/A' }}</strong></td>
                <td>
                    <span class="badge" style="background: {{ $typeColors[$movement->type] ?? '#7f8c8d' }}; color: white;">
                        {{ ucfirst($movement->type) }}
                    </span>
                </td>
                <td>{{ $movement->product?->name ?? 'N/A' }}</td>
                <td>{{ $movement->warehouse?->name ?? 'N/A' }}</td>
                <td class="text-right">
                    <strong style="color: {{ $typeColors[$movement->type] ?? '#7f8c8d' }}">
                        {{ $movement->type == 'out' ? '-' : '+' }}{{ $movement->qty }}
                    </strong>
                </td>
                <td>{{ $movement->user?->name ?? 'System' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 10px; color: #bdc3c7;"><i class="fas fa-chart-bar"></i></div>
                    <p>No stock movements found for the selected period</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
