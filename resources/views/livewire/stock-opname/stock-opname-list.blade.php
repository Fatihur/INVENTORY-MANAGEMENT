<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Stock Opname</h2>
        <button wire:click="create" class="btn btn-primary">+ New Stock Opname</button>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\StockOpname::count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Total Opname</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\StockOpname::where('status', 'pending')->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Pending</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\StockOpname::where('status', 'completed')->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Completed</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">
                    Rp {{ number_format(\App\Models\StockOpname::where('status', 'completed')->get()->sum(fn($o) => abs($o->variance_qty) * ($o->product?->purchase_price ?? 0)), 0, ',', '.') }}
                </div>
                <div style="font-size: 12px; opacity: 0.9;">Total Variance Value</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search opname # or product..." class="form-control" style="width: 220px;">

            <select wire:model.live="warehouseFilter" class="form-control" style="width: 160px;">
                <option value="all">All Warehouses</option>
                @foreach($warehousesList as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" class="form-control" style="width: 140px;">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select wire:model.live="perPage" class="form-control" style="width: 120px;">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
            </select>

            <button wire:click="$refresh" class="btn btn-default">Refresh</button>
        </div>
    </div>

    <!-- Table -->
    <div class="panel">
        <div class="panel-header">Stock Opname List</div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('opname_number')">
                            Opname #
                            @if($sortBy === 'opname_number'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th class="text-right">System</th>
                        <th class="text-right">Actual</th>
                        <th class="text-right">Variance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockOpnames as $opname)
                    <tr>
                        <td>
                            <span style="font-weight: bold; color: #3498db;">{{ $opname->opname_number }}</span>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $opname->created_at->format('d M Y') }}</div>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $opname->product?->name ?? 'N/A' }}</div>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $opname->product?->sku ?? '' }}</div>
                            @if($opname->batch)
                            <div style="font-size: 10px; color: #3498db;">Batch: {{ $opname->batch->batch_number }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $opname->warehouse?->name ?? 'N/A' }}
                        </td>
                        <td class="text-right">
                            <strong>{{ $opname->system_qty }}</strong>
                        </td>
                        <td class="text-right">
                            <strong style="color: {{ $opname->actual_qty != $opname->system_qty ? '#3498db' : 'inherit' }}">
                                {{ $opname->actual_qty }}
                            </strong>
                        </td>
                        <td class="text-right">
                            @php
                                $varianceColor = $opname->variance_qty == 0 ? '#27ae60' : ($opname->variance_qty > 0 ? '#3498db' : '#e74c3c');
                            @endphp
                            <strong style="color: {{ $varianceColor }}">
                                {{ $opname->variance_qty > 0 ? '+' : '' }}{{ $opname->variance_qty }}
                            </strong>
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'pending' => ['class' => 'badge-warning', 'label' => 'Pending'],
                                    'completed' => ['class' => 'badge-success', 'label' => 'Completed'],
                                    'cancelled' => ['class' => 'badge-danger', 'label' => 'Cancelled'],
                                ];
                                $config = $statusConfig[$opname->status] ?? $statusConfig['pending'];
                            @endphp
                            <span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
                        </td>
                        <td>
                            <a href="#" wire:click.prevent="view({{ $opname->id }})" style="color: #3498db;">View</a>
                            @if($opname->status === 'pending')
                            <a href="#" wire:click.prevent="edit({{ $opname->id }})" style="color: #27ae60; margin-left: 8px;">Edit</a>
                            <a href="#" wire:click.prevent="confirmComplete({{ $opname->id }})" style="color: #3498db; margin-left: 8px;">Complete</a>
                            <a href="#" wire:click.prevent="confirmDelete({{ $opname->id }})" style="color: #e74c3c; margin-left: 8px;">Delete</a>
                            @endif
                            @if(in_array($opname->status, ['pending', 'completed']))
                            <a href="#" wire:click.prevent="cancel({{ $opname->id }})" style="color: #e74c3c; margin-left: 8px;">Cancel</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-chart-bar" style="color: #bdc3c7;"></i></div>
                            <p>No stock opname found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stockOpnames->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $stockOpnames->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $stockOpnames->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $stockOpnames->total() }}</strong> results
                </div>
                <div>
                    {{ $stockOpnames->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showModal', false)">
        <div style="background: #fff; width: 600px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $opnameId ? 'Edit Stock Opname' : 'New Stock Opname' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;">
                            <label class="form-label">Warehouse <span style="color: #e74c3c;">*</span></label>
                            <select wire:model.live="warehouse_id" class="form-control">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehousesList as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="form-label">Product <span style="color: #e74c3c;">*</span></label>
                            <select wire:model.live="product_id" class="form-control">
                                <option value="">-- Select Product --</option>
                                @foreach($productsList as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">System Quantity</label>
                            <input type="number" wire:model="system_qty" readonly class="form-control" style="background: #ecf0f1;">
                            <div style="font-size: 10px; color: #7f8c8d; margin-top: 3px;">Auto-calculated from stock</div>
                        </div>

                        <div>
                            <label class="form-label">Actual Quantity <span style="color: #e74c3c;">*</span></label>
                            <input type="number" wire:model="actual_qty" min="0" class="form-control">
                            @error('actual_qty') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        @if($actual_qty != $system_qty)
                        <div style="grid-column: span 2; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 10px;">
                            <div style="font-size: 11px; color: #856404;">
                                <strong>Variance Detected:</strong> {{ $actual_qty - $system_qty > 0 ? '+' : '' }}{{ $actual_qty - $system_qty }} units
                            </div>
                        </div>
                        @endif

                        <div style="grid-column: span 2;">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" rows="2" placeholder="Reason for variance, observations, etc." class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $opnameId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
    @php
        $opname = \App\Models\StockOpname::with(['product', 'warehouse', 'creator', 'approver'])->find($opnameId);
        $varianceColor = $opname?->variance_qty == 0 ? '#27ae60' : ($opname?->variance_qty > 0 ? '#3498db' : '#e74c3c');
    @endphp
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showViewModal', false)">
        <div style="background: #fff; width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Stock Opname Details</span>
                <button wire:click="$set('showViewModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            @if($opname)
            <div style="padding: 20px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #ecf0f1; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-chart-bar" style="color: #3498db;"></i></div>
                    <h3 style="font-size: 16px; font-weight: bold;">{{ $opname->opname_number }}</h3>
                    <span class="badge {{ $opname->status === 'completed' ? 'badge-success' : ($opname->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                        {{ ucfirst($opname->status) }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="grid-column: span 2;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Product</div>
                        <div style="font-weight: bold;">{{ $opname->product?->name ?? 'N/A' }}</div>
                        <div style="font-size: 10px; color: #7f8c8d;">{{ $opname->product?->sku ?? '' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Warehouse</div>
                        <div style="font-weight: bold;">{{ $opname->warehouse?->name ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Created By</div>
                        <div style="font-weight: bold;">{{ $opname->creator?->name ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">System Qty</div>
                        <div style="font-weight: bold;">{{ $opname->system_qty }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Actual Qty</div>
                        <div style="font-weight: bold; color: #3498db;">{{ $opname->actual_qty }}</div>
                    </div>

                    <div style="grid-column: span 2; background: #f8f9fa; padding: 15px; border-radius: 4px;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Variance</div>
                        <div style="font-size: 24px; font-weight: bold; color: {{ $varianceColor }};">
                            {{ $opname->variance_qty > 0 ? '+' : '' }}{{ $opname->variance_qty }}
                        </div>
                        @if($opname->variance_qty != 0)
                        <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                            {{ abs($opname->variance_qty) }} units {{ $opname->variance_qty > 0 ? 'surplus' : 'shortage' }}
                        </div>
                        @endif
                    </div>

                    @if($opname->completed_at)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Completed At</div>
                        <div style="font-weight: bold;">{{ $opname->completed_at->format('d M Y H:i') }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Approved By</div>
                        <div style="font-weight: bold;">{{ $opname->approver?->name ?? 'N/A' }}</div>
                    </div>
                    @endif

                    @if($opname->notes)
                    <div style="grid-column: span 2; padding-top: 15px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Notes</div>
                        <div style="margin-top: 5px;">{{ $opname->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                @if($opname->status === 'pending')
                <button wire:click="edit({{ $opnameId }})" class="btn btn-success">Edit</button>
                @endif
                <button wire:click="$set('showViewModal', false)" class="btn btn-default">Close</button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Complete Confirmation Modal -->
    @if($showCompleteModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showCompleteModal', false)">
        <div style="background: #fff; width: 400px; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="background: #27ae60; color: white;">Confirm Complete</div>
            <div style="padding: 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #27ae60;"></i></div>
                    <p>Are you sure you want to complete this stock opname?</p>
                    <p style="color: #7f8c8d; font-size: 12px;">This will update the actual stock quantity in the system.</p>
                </div>
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button wire:click="$set('showCompleteModal', false)" class="btn btn-default">Cancel</button>
                    <button wire:click="complete" class="btn btn-success">Complete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showDeleteModal', false)">
        <div style="background: #fff; width: 400px; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="background: #e74c3c; color: white;">Confirm Delete</div>
            <div style="padding: 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i></div>
                    <p>Are you sure you want to delete this stock opname?</p>
                    <p style="color: #7f8c8d; font-size: 12px;">This action cannot be undone.</p>
                </div>
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button wire:click="$set('showDeleteModal', false)" class="btn btn-default">Cancel</button>
                    <button wire:click="delete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        $wire.on('stock-opname-created', () => {
            $wire.$refresh();
        });
        $wire.on('stock-opname-updated', () => $wire.$refresh());
        $wire.on('stock-opname-completed', () => $wire.$refresh());
        $wire.on('stock-opname-cancelled', () => $wire.$refresh());
        $wire.on('stock-opname-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
