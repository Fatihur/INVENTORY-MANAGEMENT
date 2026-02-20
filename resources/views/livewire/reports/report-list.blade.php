<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Reports</h2>
    </div>

    <!-- Report Type Cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
        <div wire:click="setReport('inventory')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'inventory' ? '#3498db' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'inventory' ? '#ebf5fb' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #3498db;"><i class="fas fa-box"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Inventory Report</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Stock levels and valuation</p>
        </div>

        <div wire:click="setReport('sales')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'sales' ? '#27ae60' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'sales' ? '#eafaf1' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #27ae60;"><i class="fas fa-shopping-cart"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Sales Report</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Sales analysis and trends</p>
        </div>

        <div wire:click="setReport('purchase')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'purchase' ? '#9b59b6' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'purchase' ? '#f5eef8' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #9b59b6;"><i class="fas fa-file-alt"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Purchase Report</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Purchase history and analysis</p>
        </div>

        <div wire:click="setReport('low_stock')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'low_stock' ? '#e74c3c' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'low_stock' ? '#fdedec' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Low Stock Alert</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Products below minimum stock</p>
        </div>

        <div wire:click="setReport('expiry')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'expiry' ? '#f39c12' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'expiry' ? '#fef5e7' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #f39c12;"><i class="fas fa-clock"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Expiry Report</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Batches expiring soon</p>
        </div>

        <div wire:click="setReport('movement')" style="cursor: pointer; border: 2px solid {{ $activeReport === 'movement' ? '#1abc9c' : '#ecf0f1' }}; border-radius: 8px; padding: 20px; text-align: center; background: {{ $activeReport === 'movement' ? '#e8f8f5' : 'white' }}; transition: all 0.3s;">
            <div style="font-size: 36px; margin-bottom: 10px; color: #1abc9c;"><i class="fas fa-chart-bar"></i></div>
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Movement Report</h3>
            <p style="color: #7f8c8d; font-size: 11px;">Stock in/out movements</p>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="panel" style="margin-bottom: 20px;">
        <div class="panel-header">Report Filters</div>
        <div class="panel-body">
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                @if(in_array($activeReport, ['sales', 'purchase', 'movement']))
                <div>
                    <label class="form-label">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>

                <div>
                    <label class="form-label">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                @endif

                @if(in_array($activeReport, ['inventory', 'movement']))
                <div>
                    <label class="form-label">Warehouse</label>
                    <select wire:model.live="warehouseFilter" class="form-control">
                        <option value="all">All Warehouses</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($activeReport === 'inventory')
                <div>
                    <label class="form-label">Product</label>
                    <select wire:model.live="productFilter" class="form-control">
                        <option value="all">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="form-label">Export Format</label>
                    <select wire:model.live="exportFormat" class="form-control">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF Document</option>
                    </select>
                </div>

                <button wire:click="export" wire:loading.attr="disabled" class="btn btn-primary">
                    <i class="fas fa-download" wire:loading.remove></i>
                    <i class="fas fa-spinner fa-spin" wire:loading></i>
                    <span wire:loading>Generating...</span>
                    <span wire:loading.remove>Generate Report</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="panel">
        <div class="panel-header">
            @switch($activeReport)
            @case('inventory')
                Inventory Report
                @break
            @case('sales')
                Sales Report
                @break
            @case('purchase')
                Purchase Report
                @break
            @case('low_stock')
                Low Stock Alert
                @break
            @case('expiry')
                Expiry Report
                @break
            @case('movement')
                Movement Report
                @break
            @default
                Report
            @endswitch
        </div>

        <div class="panel-body">
            @switch($activeReport)
            @case('inventory')
                @include('livewire.reports.partials.inventory-report')
                @break
            @case('sales')
                @include('livewire.reports.partials.sales-report')
                @break
            @case('purchase')
                @include('livewire.reports.partials.purchase-report')
                @break
            @case('low_stock')
                @include('livewire.reports.partials.low-stock-report')
                @break
            @case('expiry')
                @include('livewire.reports.partials.expiry-report')
                @break
            @case('movement')
                @include('livewire.reports.partials.movement-report')
                @break
            @default
                <p>Select a report type above.</p>
            @endswitch
        </div>
    </div>

@script
<script>
    $wire.on('report-generated', ({ filename }) => {
        // Trigger file download
        const downloadUrl = '{{ route('reports.download', ['filename' => '__FILENAME__']) }}'.replace('__FILENAME__', filename);
        window.location.href = downloadUrl;
    });
</script>
@endscript
</div>
