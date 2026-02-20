<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
            <h2 style="font-size: 14px; font-weight: bold;">{{ $supplier->name }}</h2>
            <p style="color: #7f8c8d; font-size: 11px;">Manage Supplier Products</p>
        </div>
        <a wire:navigate href="{{ route('suppliers.index') }}" class="btn btn-default">Back</a>
    </div>

    @if($statusMessage)
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            {{ $statusMessage }}
        </div>
    @endif

    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-header">Add Product</div>
        <div class="panel-body">
            <form wire:submit="addProduct">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                    <div class="form-group">
                        <label class="form-label">Product</label>
                        <select wire:model="selectedProductId" class="form-control">
                            <option value="">Select Product</option>
                            @foreach($availableProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedProductId')
                            <span style="color: #e74c3c; font-size: 10px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Buy Price</label>
                        <input type="number" wire:model="buyPrice" step="0.01" min="0" class="form-control">
                        @error('buyPrice')
                            <span style="color: #e74c3c; font-size: 10px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">MOQ</label>
                        <input type="number" wire:model="moq" min="1" class="form-control">
                        @error('moq')
                            <span style="color: #e74c3c; font-size: 10px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lead Time (Days)</label>
                        <input type="number" wire:model="leadTimeDays" min="1" class="form-control">
                        @error('leadTimeDays')
                            <span style="color: #e74c3c; font-size: 10px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">Supplier Products</div>
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Buy Price</th>
                        <th>MOQ</th>
                        <th>Lead Time</th>
                        <th>Primary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>Rp {{ number_format($product->pivot->buy_price, 0) }}</td>
                        <td>{{ $product->pivot->moq }}</td>
                        <td>{{ $product->pivot->lead_time_days }} days</td>
                        <td>
                            @if($product->pivot->is_primary)
                                <span class="badge badge-success">Primary</span>
                            @else
                                <button wire:click="setPrimary({{ $product->id }})" class="btn btn-default" style="padding: 4px 8px; font-size: 10px;">Set Primary</button>
                            @endif
                        </td>
                        <td>
                            <button wire:click="removeProduct({{ $product->id }})" class="btn btn-danger" style="padding: 4px 8px; font-size: 10px;">Remove</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #7f8c8d;">No products assigned to this supplier</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
