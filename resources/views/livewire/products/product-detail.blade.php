<div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
        <div>
            <h2 style="font-size: 14px; font-weight: bold;">{{ $product->name }}</h2>
            <p style="color: #7f8c8d; font-size: 11px;">SKU: {{ $product->sku }}</p>
        </div>
        <div style="display: flex; gap: 10px;">
            @can('products.edit')
                <a wire:navigate href="{{ route('products.edit', $product) }}" class="btn btn-primary">Edit</a>
            @endcan
            <a wire:navigate href="{{ route('products.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>

    <div style="display: flex; gap: 15px;">
        <div style="flex: 1;">
            <div class="panel">
                <div class="panel-header">Product Information</div>
                <div class="panel-body">
                    <table class="data-table" style="border: none;">
                        <tr>
                            <td style="width: 120px; color: #7f8c8d;">Unit:</td>
                            <td>{{ $product->unit }}</td>
                        </tr>
                        <tr>
                            <td style="color: #7f8c8d;">Category:</td>
                            <td>{{ $product->category ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #7f8c8d; vertical-align: top;">Description:</td>
                            <td>{{ $product->description ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div style="flex: 1;">
            <div class="panel">
                <div class="panel-header">Stock Information</div>
                <div class="panel-body">
                    <table class="data-table" style="border: none;">
                        <tr>
                            <td style="width: 120px; color: #7f8c8d;">Current Stock:</td>
                            <td style="font-weight: bold;">{{ $product->total_stock }}</td>
                        </tr>
                        <tr>
                            <td style="color: #7f8c8d;">Min Stock:</td>
                            <td>{{ $product->min_stock }}</td>
                        </tr>
                        <tr>
                            <td style="color: #7f8c8d;">Safety Stock:</td>
                            <td>{{ $product->safety_stock }}</td>
                        </tr>
                        <tr>
                            <td style="color: #7f8c8d;">Lead Time:</td>
                            <td>{{ $product->lead_time_days }} days</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 15px;">
        <div class="panel">
            <div class="panel-header">Stock by Warehouse</div>
            <div class="panel-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Quantity</th>
                            <th>Reserved</th>
                            <th>Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->stocks as $stock)
                        <tr>
                            <td>{{ $stock->warehouse->name }}</td>
                            <td>{{ $stock->qty_on_hand }}</td>
                            <td>{{ $stock->qty_reserved }}</td>
                            <td>{{ $stock->qty_available }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
