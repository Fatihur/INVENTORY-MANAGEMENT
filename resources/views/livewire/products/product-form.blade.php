<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">{{ $product ? 'Edit Product' : 'Add Product' }}</h2>

    <form wire:submit="save">
        <div class="panel">
            <div class="panel-header">Product Information</div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" wire:model="sku" class="form-control">
                        @error('sku') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" wire:model="name" class="form-control">
                        @error('name') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" wire:model="unit" class="form-control" placeholder="e.g., pcs, kg, box">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" wire:model="category" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Min Stock</label>
                        <input type="number" wire:model="min_stock" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Safety Stock</label>
                        <input type="number" wire:model="safety_stock" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lead Time (Days)</label>
                        <input type="number" wire:model="lead_time_days" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Stock</label>
                        <input type="number" wire:model="target_stock" class="form-control">
                    </div>
                </div>

                <div style="margin-top: 15px; display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" wire:model="track_batch" style="margin-right: 5px;">
                        Track Batch
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" wire:model="is_active" style="margin-right: 5px;">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">{{ $product ? 'Update' : 'Create' }} Product</button>
            <a wire:navigate href="{{ route('products.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
