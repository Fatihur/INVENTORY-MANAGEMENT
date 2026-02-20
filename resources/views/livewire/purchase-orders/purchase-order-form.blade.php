<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">{{ $purchaseOrder ? 'Edit' : 'Create' }} Purchase Order</h2>

    <form wire:submit="save">
        <div class="panel">
            <div class="panel-header">PO Information</div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Supplier <span style="color: #e74c3c;">*</span></label>
                        <select wire:model="supplier_id" class="form-control">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Order Date <span style="color: #e74c3c;">*</span></label>
                        <input type="date" wire:model="order_date" class="form-control">
                        @error('order_date') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" wire:model="expected_delivery_date" class="form-control">
                        @error('expected_delivery_date') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top: 15px;">
            <div class="panel-header">Items <span style="color: #e74c3c;">*</span></div>
            <div class="panel-body">
                @error('items')
                    <div style="color: #e74c3c; font-size: 11px; margin-bottom: 10px;">{{ $message }}</div>
                @enderror

                <table class="data-table" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Product</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 20%;">Unit Price</th>
                            <th style="width: 20%;">Total</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr>
                            <td>
                                <select wire:model="items.{{ $index }}.product_id" class="form-control">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.product_id")
                                    <div style="color: #e74c3c; font-size: 10px;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number" wire:model="items.{{ $index }}.qty_ordered" min="1" class="form-control">
                                @error("items.{$index}.qty_ordered")
                                    <div style="color: #e74c3c; font-size: 10px;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number" wire:model="items.{{ $index }}.unit_price" min="0" step="0.01" class="form-control">
                                @error("items.{$index}.unit_price")
                                    <div style="color: #e74c3c; font-size: 10px;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td style="text-align: right; font-weight: bold;">
                                Rp {{ number_format(($item['qty_ordered'] ?? 0) * ($item['unit_price'] ?? 0), 0) }}
                            </td>
                            <td>
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-danger" style="padding: 4px 8px; font-size: 10px;">X</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="button" wire:click="addItem" style="color: #3498db; background: none; border: none; cursor: pointer;">+ Add Item</button>
            </div>
        </div>

        <div class="panel" style="margin-top: 15px;">
            <div class="panel-header">Summary</div>
            <div class="panel-body">
                <table style="width: 100%; max-width: 300px; margin-left: auto;">
                    <tr>
                        <td style="text-align: right; padding: 5px;">Subtotal:</td>
                        <td style="text-align: right; padding: 5px; width: 120px;">Rp {{ number_format($subtotal, 0) }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 5px;">Tax (11%):</td>
                        <td style="text-align: right; padding: 5px;">Rp {{ number_format($tax, 0) }}</td>
                    </tr>
                    <tr style="font-weight: bold; font-size: 14px;">
                        <td style="text-align: right; padding: 5px;">Total:</td>
                        <td style="text-align: right; padding: 5px;">Rp {{ number_format($total, 0) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">{{ $purchaseOrder ? 'Update' : 'Create' }} PO</button>
            <a wire:navigate href="{{ route('purchase-orders.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
