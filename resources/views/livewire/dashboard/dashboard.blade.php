<div>
    <!-- KPI Cards -->
    <div class="grid grid-cols-4" style="margin-bottom: 20px;">
        <div class="panel stat-card">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 11px; color: #7f8c8d;">Total Products</div>
                <div class="text-2xl" style="font-size: 24px; font-weight: bold; color: #2c3e50;">{{ \App\Models\Product::count() }}</div>
            </div>
        </div>
        <div class="panel stat-card">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 11px; color: #7f8c8d;">Low Stock</div>
                <div class="text-2xl" style="font-size: 24px; font-weight: bold; color: #f39c12;">{{ \App\Models\Product::lowStock()->count() }}</div>
            </div>
        </div>
        <div class="panel stat-card">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 11px; color: #7f8c8d;">Out of Stock</div>
                <div class="text-2xl" style="font-size: 24px; font-weight: bold; color: #e74c3c;">{{ \App\Models\Product::outOfStock()->count() }}</div>
            </div>
        </div>
        <div class="panel stat-card">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 11px; color: #7f8c8d;">Pending POs</div>
                <div class="text-2xl" style="font-size: 24px; font-weight: bold; color: #3498db;">{{ \App\Models\PurchaseOrder::whereIn('status', ['draft', 'sent', 'approved'])->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activities -->
    <div class="responsive-flex">
        <div>
            <div class="panel">
                <div class="panel-header">Quick Actions</div>
                <div class="panel-body">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @can('products.create')
                            <a wire:navigate href="{{ route('products.create') }}" class="btn btn-primary" style="text-align: center;">+ Add New Product</a>
                        @endcan
                        @can('stock.scanner')
                            <a wire:navigate href="{{ route('stock.scanner') }}" class="btn btn-success" style="text-align: center; background-color: #27ae60; color: #fff; border-color: #229954;">Scan QR Code</a>
                        @endcan
                        @can('restock.view')
                            <a wire:navigate href="{{ route('restock.recommendations') }}" class="btn" style="text-align: center; background-color: #f39c12; color: #fff;">View Restock Recommendations</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 2;">
            <div class="panel">
                <div class="panel-header">Recent Activities</div>
                <div class="panel-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\StockMovement::with(['product', 'warehouse'])->latest()->take(5)->get() as $movement)
                                <tr>
                                    <td>{{ $movement->product->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'danger' : 'info') }}">
                                            {{ strtoupper($movement->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>{{ $movement->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #7f8c8d;">No recent activities</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
