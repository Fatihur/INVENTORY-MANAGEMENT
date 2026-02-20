<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Warehouses</h2>
        @can('warehouses.create')
        <button wire:click="create" class="btn btn-primary">+ Add Warehouse</button>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Warehouse::count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Total Warehouses</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Warehouse::where('is_active', true)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Active</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Warehouse::where('is_active', false)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Inactive</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\Stock::distinct('warehouse_id')->count('warehouse_id') }}</div>
                <div style="font-size: 12px; opacity: 0.9;">With Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search code, name, address..." class="form-control" style="width: 250px;">
            <select wire:model.live="status" class="form-control" style="width: 150px;">
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
        <div class="panel-header">Warehouse List</div>
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('code')">
                            Code
                            @if($sortBy === 'code'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th style="cursor: pointer;" wire:click="sort('name')">
                            Name
                            @if($sortBy === 'name'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Address</th>
                        <th style="cursor: pointer;" wire:click="sort('is_active')">
                            Status
                            @if($sortBy === 'is_active'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Stock Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                    <tr>
                        <td>
                            <span style="font-weight: bold; color: #3498db;">{{ $warehouse->code }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $warehouse->name }}</div>
                        </td>
                        <td>
                            <div style="color: #7f8c8d; font-size: 11px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $warehouse->address ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $warehouse->id }})"
                                class="badge {{ $warehouse->is_active ? 'badge-success' : 'badge-warning' }}"
                                style="cursor: pointer; border: none;">
                                {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $warehouse->stocks()->count() }} items</span>
                        </td>
                        <td>
                            @can('warehouses.edit')
                            <a href="#" wire:click.prevent="edit({{ $warehouse->id }})" style="color: #3498db;">Edit</a>
                            @endcan
                            @can('warehouses.delete')
                            <a href="#" wire:click.prevent="confirmDelete({{ $warehouse->id }})" style="color: #e74c3c; margin-left: 10px;"
                                onclick="event.stopImmediatePropagation()">Delete</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-warehouse" style="color: #bdc3c7;"></i></div>
                            <p>No warehouses found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warehouses->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $warehouses->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $warehouses->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $warehouses->total() }}</strong> results
                </div>
                <div>
                    {{ $warehouses->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showModal', false)">
        <div style="background: #fff; width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $warehouseId ? 'Edit Warehouse' : 'Add New Warehouse' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label class="form-label">Warehouse Code <span style="color: #e74c3c;">*</span></label>
                            <input type="text" wire:model="code" placeholder="WH001" class="form-control" autofocus {{ $warehouseId ? 'readonly' : '' }}>
                            @error('code') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Warehouse Name <span style="color: #e74c3c;">*</span></label>
                            <input type="text" wire:model="name" placeholder="Main Warehouse" class="form-control">
                            @error('name') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Address</label>
                            <textarea wire:model="address" rows="3" placeholder="Enter warehouse address..." class="form-control"></textarea>
                            @error('address') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Status</label>
                            <div style="margin-top: 5px;">
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" wire:model="is_active" style="width: 16px; height: 16px;">
                                    <span>Active Warehouse</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $warehouseId ? 'Update Warehouse' : 'Create Warehouse' }}</button>
                </div>
            </form>
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
                    <p>Are you sure you want to delete this warehouse?</p>
                    <p style="color: #7f8c8d; font-size: 12px;">This action cannot be undone. Warehouses with existing stocks cannot be deleted.</p>
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
        $wire.on('warehouse-created', () => $wire.$refresh());
        $wire.on('warehouse-updated', () => $wire.$refresh());
        $wire.on('warehouse-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
