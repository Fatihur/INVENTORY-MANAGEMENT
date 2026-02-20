<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Stock Overview</h2>
        <div class="action-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a wire:navigate href="{{ route('stock.scanner') }}" class="btn btn-success" style="background-color: #27ae60; color: #fff; border-color: #229954;">Scanner</a>
            <a wire:navigate href="{{ route('stock.in') }}" class="btn btn-primary">Stock In</a>
            <a wire:navigate href="{{ route('stock.out') }}" class="btn" style="background-color: #e67e22; color: #fff;">Stock Out</a>
            <a wire:navigate href="{{ route('stock.transfer') }}" class="btn" style="background-color: #9b59b6; color: #fff;">Transfer</a>
            <a wire:navigate href="{{ route('stock.adjust') }}" class="btn btn-default">Adjust</a>
        </div>
    </div>

    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>On Hand</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr>
                        <td>{{ $stock->product->name }}</td>
                        <td>{{ $stock->warehouse->name }}</td>
                        <td>{{ $stock->qty_on_hand }}</td>
                        <td>{{ $stock->qty_reserved }}</td>
                        <td>{{ $stock->qty_available }}</td>
                        <td>
                            @if($stock->isOutOfStock())
                                <span class="badge badge-danger">Out of Stock</span>
                            @elseif($stock->isLowStock())
                                <span class="badge badge-warning">Low Stock</span>
                            @else
                                <span class="badge badge-success">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d;">No stock records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">{{ $stocks->links() }}</div>
    </div>
</div>
