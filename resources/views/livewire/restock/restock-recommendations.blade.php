<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Restock Recommendations</h2>
        @if(count($selectedItems) > 0)
            <button wire:click="$set('showGenerateModal', true)" class="btn btn-primary">Generate PO ({{ count($selectedItems) }} items)</button>
        @endif
    </div>

    <div class="panel-body" style="background-color: #fff; border: 1px solid #bdc3c7; padding: 10px; margin-bottom: 15px;">
        <select wire:model.live="priorityFilter" class="form-control" style="width: 150px;">
            <option value="all">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
    </div>

    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" wire:click="$toggle('selectedItems')"></th>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Reorder Point</th>
                        <th>ADU</th>
                        <th>Stockout Date</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recommendations as $rec)
                    <tr style="{{ $rec->priority === 'critical' ? 'background-color: #ffe6e6;' : ($rec->priority === 'high' ? 'background-color: #fff3e6;' : '') }}">
                        <td>
                            <input type="checkbox" wire:model.live="selectedItems" value="{{ $rec->product->id }}">
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $rec->product->name }}</div>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $rec->product->sku }}</div>
                        </td>
                        <td>{{ $rec->metrics['current_stock'] }}</td>
                        <td>{{ $rec->metrics['reorder_point'] }}</td>
                        <td>{{ $rec->metrics['adu'] }}</td>
                        <td>{{ $rec->metrics['estimated_stockout_date'] ?? 'N/A' }}</td>
                        <td>
                            @php
                                $priorityColors = [
                                    'critical' => 'badge-danger',
                                    'high' => 'badge-warning',
                                    'medium' => 'badge-info',
                                    'low' => 'badge-success',
                                ];
                                $badgeClass = $priorityColors[$rec->priority] ?? 'badge-default';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($rec->priority) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #7f8c8d;">No restock recommendations</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showGenerateModal)
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
        <div style="background-color: #fff; border: 1px solid #bdc3c7; padding: 20px; width: 400px;">
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">Generate Purchase Orders</h3>
            <p style="margin-bottom: 15px;">This will create purchase orders grouped by supplier for {{ count($selectedItems) }} items.</p>
            <div style="display: flex; gap: 10px;">
                <button wire:click="generatePurchaseOrders" class="btn btn-primary">Confirm</button>
                <button wire:click="$set('showGenerateModal', false)" class="btn btn-default">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
