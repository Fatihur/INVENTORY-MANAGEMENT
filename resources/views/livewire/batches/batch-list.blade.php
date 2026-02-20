<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Batches</h2>
        <button wire:click="create" class="btn btn-primary">+ Add Batch</button>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Batch::count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Total Batches</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Batch::where('expiry_date', '<', now())->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Expired</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Batch::where('expiry_date', '>=', now())->where('expiry_date', '<=', now()->addDays(30))->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Expiring (30 days)</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Batch::where('remaining_qty', '>', 0)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">In Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search batch number..." class="form-control" style="width: 200px;">

            <select wire:model.live="productFilter" class="form-control" style="width: 180px;">
                <option value="all">All Products</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="warehouseFilter" class="form-control" style="width: 160px;">
                <option value="all">All Warehouses</option>
                @foreach($warehousesList as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="expiryFilter" class="form-control" style="width: 160px;">
                <option value="all">All Expiry</option>
                <option value="expired">Expired</option>
                <option value="expiring_30">Expiring (30 days)</option>
                <option value="expiring_60">Expiring (60 days)</option>
                <option value="expiring_90">Expiring (90 days)</option>
                <option value="no_expiry">No Expiry</option>
            </select>

            <select wire:model.live="statusFilter" class="form-control" style="width: 140px;">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
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
        <div class="panel-header">Batch Management</div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('batch_number')">
                            Batch #
                            @if($sortBy === 'batch_number'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Product</th>
                        <th style="cursor: pointer;" wire:click="sort('manufacturing_date')">
                            Mfg Date
                            @if($sortBy === 'manufacturing_date'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th style="cursor: pointer;" wire:click="sort('expiry_date')">
                            Expiry Date
                            @if($sortBy === 'expiry_date'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th class="text-right">Qty</th>
                        <th>Warehouse</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    @php
                        $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();
                        $isExpiringSoon = $batch->expiry_date && $batch->expiry_date->diffInDays(now()) <= 30 && !$isExpired;
                    @endphp
                    <tr>
                        <td>
                            <span style="font-weight: bold; color: #3498db;">{{ $batch->batch_number }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $batch->product?->name ?? 'N/A' }}</div>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $batch->product?->sku ?? '' }}</div>
                        </td>
                        <td>
                            {{ $batch->manufacturing_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td>
                            @if($batch->expiry_date)
                                <div style="color: {{ $isExpired ? '#e74c3c' : ($isExpiringSoon ? '#f39c12' : '#27ae60') }};">
                                    {{ $batch->expiry_date->format('d M Y') }}
                                    @if($isExpired)
                                        <span class="badge badge-danger" style="margin-left: 5px;">EXPIRED</span>
                                    @elseif($isExpiringSoon)
                                        <span class="badge badge-warning" style="margin-left: 5px;">{{ $batch->expiry_date->diffInDays(now()) }} days</span>
                                    @endif
                                </div>
                            @else
                                <span style="color: #7f8c8d;">-</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div style="font-weight: bold;">{{ $batch->remaining_qty }} / {{ $batch->initial_qty }}</div>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $batch->initial_qty > 0 ? round(($batch->remaining_qty / $batch->initial_qty) * 100, 1) : 0 }}%</div>
                        </td>
                        <td>
                            {{ $batch->warehouse?->name ?? 'N/A' }}
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $batch->id }})"
                                class="badge {{ $batch->is_active ? 'badge-success' : 'badge-warning' }}"
                                style="cursor: pointer; border: none;">
                                {{ $batch->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td>
                            <a href="#" wire:click.prevent="view({{ $batch->id }})" style="color: #3498db;">View</a>
                            <a href="#" wire:click.prevent="edit({{ $batch->id }})" style="color: #27ae60; margin-left: 8px;">Edit</a>
                            <a href="#" wire:click.prevent="confirmDelete({{ $batch->id }})" style="color: #e74c3c; margin-left: 8px;">Delete</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-tag" style="color: #bdc3c7;"></i></div>
                            <p>No batches found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $batches->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $batches->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $batches->total() }}</strong> results
                </div>
                <div>
                    {{ $batches->links() }}
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
                <span>{{ $batchId ? 'Edit Batch' : 'Add New Batch' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;">
                            <label class="form-label">Product <span style="color: #e74c3c;">*</span></label>
                            <select wire:model="product_id" class="form-control">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="form-label">Batch Number <span style="color: #e74c3c;">*</span></label>
                            <input type="text" wire:model="batch_number" placeholder="Leave empty for auto-generated" class="form-control">
                            @error('batch_number') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                            <div style="font-size: 10px; color: #7f8c8d; margin-top: 3px;">Leave empty to auto-generate (BATCH000001)</div>
                        </div>

                        <div>
                            <label class="form-label">Manufacturing Date</label>
                            <input type="date" wire:model="manufacturing_date" class="form-control">
                            @error('manufacturing_date') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input type="date" wire:model="expiry_date" class="form-control">
                            @error('expiry_date') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Initial Quantity <span style="color: #e74c3c;">*</span></label>
                            <input type="number" wire:model="initial_qty" min="0" class="form-control">
                            @error('initial_qty') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Remaining Quantity <span style="color: #e74c3c;">*</span></label>
                            <input type="number" wire:model="remaining_qty" min="0" class="form-control">
                            @error('remaining_qty') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Warehouse</label>
                            <select wire:model="warehouse_id" class="form-control">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehousesList as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Cost Price</label>
                            <input type="number" step="0.01" wire:model="cost_price" min="0" class="form-control">
                            @error('cost_price') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" rows="2" class="form-control"></textarea>
                        </div>

                        <div>
                            <label class="form-label">Status</label>
                            <div style="margin-top: 5px;">
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" wire:model="is_active" style="width: 16px; height: 16px;">
                                    <span>Active Batch</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $batchId ? 'Update Batch' : 'Create Batch' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
    @php
        $batch = \App\Models\Batch::with(['product', 'warehouse'])->find($batchId);
        $isExpired = $batch?->expiry_date && $batch->expiry_date->isPast();
        $isExpiringSoon = $batch?->expiry_date && $batch->expiry_date->diffInDays(now()) <= 30 && !$isExpired;
    @endphp
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showViewModal', false)">
        <div style="background: #fff; width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Batch Details</span>
                <button wire:click="$set('showViewModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            @if($batch)
            <div style="padding: 20px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #ecf0f1; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-tag" style="color: #3498db;"></i></div>
                    <h3 style="font-size: 18px; font-weight: bold;">{{ $batch->batch_number }}</h3>
                    <div>{{ $batch->product?->name }}</div>
                    @if($isExpired)
                    <span class="badge badge-danger" style="margin-top: 10px;">EXPIRED</span>
                    @elseif($isExpiringSoon)
                    <span class="badge badge-warning" style="margin-top: 10px;">Expiring in {{ $batch->expiry_date->diffInDays(now()) }} days</span>
                    @endif
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Manufacturing Date</div>
                        <div style="font-weight: bold;">{{ $batch->manufacturing_date?->format('d M Y') ?? '-' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Expiry Date</div>
                        <div style="font-weight: bold; color: {{ $isExpired ? '#e74c3c' : ($isExpiringSoon ? '#f39c12' : 'inherit') }};">
                            {{ $batch->expiry_date?->format('d M Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Initial Quantity</div>
                        <div style="font-weight: bold;">{{ $batch->initial_qty }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Remaining Quantity</div>
                        <div style="font-weight: bold; color: {{ $batch->remaining_qty == 0 ? '#e74c3c' : '#27ae60' }};">{{ $batch->remaining_qty }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Warehouse</div>
                        <div style="font-weight: bold;">{{ $batch->warehouse?->name ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Cost Price</div>
                        <div style="font-weight: bold;">Rp {{ number_format($batch->cost_price ?? 0, 0, ',', '.') }}</div>
                    </div>

                    <div style="grid-column: span 2;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Stock Usage</div>
                        <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden; margin-top: 5px;">
                            <div style="background: linear-gradient(90deg, #3498db, #2980b9); height: 100%; width: {{ $batch->initial_qty > 0 ? (($batch->initial_qty - $batch->remaining_qty) / $batch->initial_qty) * 100 : 0 }}%;"></div>
                        </div>
                        <div style="font-size: 10px; color: #7f8c8d; margin-top: 3px;">
                            {{ $batch->initial_qty - $batch->remaining_qty }} used ({{ $batch->initial_qty > 0 ? round((($batch->initial_qty - $batch->remaining_qty) / $batch->initial_qty) * 100, 1) : 0 }}%)
                        </div>
                    </div>

                    @if($batch->notes)
                    <div style="grid-column: span 2; padding-top: 15px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Notes</div>
                        <div style="margin-top: 5px;">{{ $batch->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                <button wire:click="edit({{ $batchId }})" class="btn btn-success">Edit</button>
                <button wire:click="$set('showViewModal', false)" class="btn btn-default">Close</button>
            </div>
            @endif
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
                    <p>Are you sure you want to delete this batch?</p>
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
        $wire.on('batch-created', () => $wire.$refresh());
        $wire.on('batch-updated', () => $wire.$refresh());
        $wire.on('batch-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
