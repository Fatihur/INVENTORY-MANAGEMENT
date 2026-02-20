<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
            <h2 style="font-size: 14px; font-weight: bold;">Goods Receipt</h2>
            <p style="color: #7f8c8d; font-size: 11px;">PO: {{ $purchaseOrder->po_number }}</p>
        </div>
        <a wire:navigate href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-default">Back</a>
    </div>

    @if($statusMessage)
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="receive">
        <div class="panel">
            <div class="panel-header">Receipt Information</div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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

                    <div class="form-group">
                        <label class="form-label">Invoice Number <span style="color: #e74c3c;">*</span></label>
                        <input type="text" wire:model="invoiceNumber" class="form-control" placeholder="INV-XXXX">
                        @error('invoiceNumber')
                            <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top: 15px;">
            <div class="panel-header">Items to Receive</div>
            <div class="panel-body" style="padding: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Ordered</th>
                            <th>Previously Received</th>
                            <th>Qty to Receive</th>
                            <th>Batch Number</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receiptItems as $index => $item)
                        <tr>
                            <td>{{ $item['product_name'] }}</td>
                            <td>{{ $item['qty_ordered'] }}</td>
                            <td>{{ $item['qty_ordered'] - $item['qty_received'] }}</td>
                            <td>
                                <input type="number" wire:model="receiptItems.{{ $index }}.qty_received" min="0" class="form-control" style="width: 80px;">
                            </td>
                            <td>
                                <input type="text" wire:model="receiptItems.{{ $index }}.batch_number" class="form-control" style="width: 120px;">
                            </td>
                            <td>
                                <input type="date" wire:model="receiptItems.{{ $index }}.expiry_date" class="form-control" style="width: 130px;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success" style="background-color: #27ae60; color: #fff;">Receive Goods</button>
            <a wire:navigate href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
