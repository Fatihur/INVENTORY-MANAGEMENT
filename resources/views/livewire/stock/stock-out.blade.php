<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">Stock Out</h2>

    @if($statusMessage)
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb;">{{ $statusMessage }}</div>
    @endif

    <form wire:submit="save">
        <div class="panel">
            <div class="panel-header">Stock Out Details</div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <select wire:model="productId" class="form-control">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('productId') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Warehouse</label>
                    <select wire:model="warehouseId" class="form-control">
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouseId') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" wire:model="quantity" min="1" class="form-control" style="width: 150px;">
                    @error('quantity') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="2" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn" style="background-color: #e67e22; color: #fff; border-color: #d35400;">Record Stock Out</button>
            <a wire:navigate href="{{ route('stock.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
