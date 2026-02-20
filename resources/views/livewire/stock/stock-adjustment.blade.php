<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">Stock Adjustment</h2>

    @if($statusMessage)
        <div style="padding: 10px; margin-bottom: 15px; border: 1px solid; {{ $statusType === 'success' ? 'background-color: #d4edda; color: #155724; border-color: #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="adjust">
        <div class="panel">
            <div class="panel-header">Adjustment Details</div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Product <span style="color: #e74c3c;">*</span></label>
                    <select wire:model="productId" class="form-control">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('productId')
                        <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Warehouse <span style="color: #e74c3c;">*</span></label>
                    <select wire:model="warehouseId" class="form-control">
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouseId')
                        <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                    @enderror
                </div>

                @if($currentStock !== null)
                <div style="background-color: #ecf0f1; padding: 10px; margin-bottom: 15px; border: 1px solid #bdc3c7;">
                    <strong>Current Stock:</strong> {{ $currentStock }}
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">New Quantity <span style="color: #e74c3c;">*</span></label>
                    <input type="number" wire:model="newQty" min="0" class="form-control" style="width: 150px;">
                    @error('newQty')
                        <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Reason <span style="color: #e74c3c;">*</span></label>
                    <select wire:model="reason" class="form-control">
                        <option value="">Select Reason</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Lost">Lost</option>
                        <option value="Found">Found</option>
                        <option value="Correction">Correction</option>
                        <option value="Expired">Expired</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('reason')
                        <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Adjust Stock</button>
            <a wire:navigate href="{{ route('stock.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
