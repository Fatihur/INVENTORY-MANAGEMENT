<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">Stock Transfer</h2>

    @if($statusMessage)
        <div style="padding: 10px; margin-bottom: 15px; border: 1px solid; {{ $statusType === 'success' ? 'background-color: #d4edda; color: #155724; border-color: #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="transfer">
        <div class="panel">
            <div class="panel-header">Transfer Details</div>
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">From Warehouse <span style="color: #e74c3c;">*</span></label>
                        <select wire:model="fromWarehouseId" class="form-control">
                            <option value="">Select Source</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('fromWarehouseId')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">To Warehouse <span style="color: #e74c3c;">*</span></label>
                        <select wire:model="toWarehouseId" class="form-control">
                            <option value="">Select Destination</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('toWarehouseId')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity <span style="color: #e74c3c;">*</span></label>
                    <input type="number" wire:model="quantity" min="1" class="form-control" style="width: 150px;">
                    @error('quantity')
                        <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="2" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Transfer Stock</button>
            <a wire:navigate href="{{ route('stock.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
