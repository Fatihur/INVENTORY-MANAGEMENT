<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">QR Scanner</h2>

    <div class="panel">
        <div class="panel-body" style="border-bottom: 1px solid #bdc3c7; display: flex; gap: 10px;">
            <select wire:model="scanMode" class="form-control" style="width: 150px;">
                <option value="view">View Only</option>
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </select>

            <select wire:model="warehouseId" class="form-control" style="width: 200px;">
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="panel-body">
            @if(!$product)
            <div style="background-color: #ecf0f1; padding: 30px; text-align: center;">
                <p style="color: #7f8c8d; margin-bottom: 15px;">Scan a QR code to view product details</p>
                <input type="text" wire:model="scannedCode" wire:change="codeScanned($event.target.value)" placeholder="Enter QR code or scan..." class="form-control" style="width: 250px; margin: 0 auto;">
            </div>
            @else
            <div>
                @if($statusMessage)
                    <div style="padding: 10px; margin-bottom: 15px; border: 1px solid; {{ $statusType === 'success' ? 'background-color: #d4edda; color: #155724; border-color: #c3e6cb;' : ($statusType === 'error' ? 'background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;' : 'background-color: #fff3cd; color: #856404; border-color: #ffeaa7;') }}">
                        {{ $statusMessage }}
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div><strong>Name:</strong> {{ $productData['name'] }}</div>
                    <div><strong>SKU:</strong> {{ $productData['sku'] }}</div>
                    <div><strong>Total Stock:</strong> {{ $productData['total_stock'] }}</div>
                    <div><strong>Min Stock:</strong> {{ $productData['min_stock'] }}</div>
                    <div><strong>Location:</strong> {{ $productData['location'] }}</div>
                    <div><strong>Supplier:</strong> {{ $productData['supplier'] }}</div>
                </div>

                @if(in_array($scanMode, ['in', 'out']))
                <div style="border-top: 1px solid #ecf0f1; padding-top: 15px;">
                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" wire:model="quantity" min="1" class="form-control" style="width: 120px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" rows="2" class="form-control"></textarea>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button wire:click="processTransaction" class="btn btn-primary">Process {{ ucfirst($scanMode) }}</button>
                        <button wire:click="resetScan" class="btn btn-default">Cancel</button>
                    </div>
                </div>
                @else
                <button wire:click="resetScan" class="btn btn-default">Scan Another</button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
