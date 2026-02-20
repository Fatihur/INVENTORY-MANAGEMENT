<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Bin Locations</h2>
        @can('bin-locations.create')
        <button wire:click="create" class="btn btn-primary">+ Add Bin Location</button>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\BinLocation::count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Total Bins</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\BinLocation::where('is_active', true)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Active</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\BinLocation::whereRaw('current_qty >= capacity')->where('capacity', '>', 0)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Full</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 24px; font-weight: bold;">{{ \App\Models\BinLocation::where('current_qty', 0)->count() }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Empty</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search code, zone, aisle..." class="form-control" style="width: 200px;">

            <select wire:model.live="warehouseFilter" class="form-control" style="width: 160px;">
                <option value="all">All Warehouses</option>
                @foreach($warehousesList as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="capacityFilter" class="form-control" style="width: 140px;">
                <option value="all">All Capacity</option>
                <option value="empty">Empty</option>
                <option value="available">Available</option>
                <option value="full">Full</option>
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
        <div class="panel-header">Bin Locations</div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('code')">
                            Code
                            @if($sortBy === 'code'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Warehouse</th>
                        <th>Location</th>
                        <th class="text-right">Capacity</th>
                        <th class="text-right">Current</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($binLocations as $binLocation)
                    @php
                        $capacityPercent = $this->getCapacityPercentage($binLocation);
                        $isFull = $binLocation->current_qty >= $binLocation->capacity && $binLocation->capacity > 0;
                        $isEmpty = $binLocation->current_qty == 0;
                        $barColor = $isFull ? '#e74c3c' : ($capacityPercent > 80 ? '#f39c12' : '#27ae60');
                    @endphp
                    <tr>
                        <td>
                            <span style="font-weight: bold; color: #3498db;">{{ $binLocation->code }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $binLocation->warehouse?->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div style="font-size: 11px;">
                                @if($binLocation->zone)
                                <span class="badge badge-info">Zone: {{ $binLocation->zone }}</span>
                                @endif
                                @if($binLocation->aisle)
                                <span class="badge badge-info">Aisle: {{ $binLocation->aisle }}</span>
                                @endif
                                @if($binLocation->rack)
                                <span class="badge badge-info">Rack: {{ $binLocation->rack }}</span>
                                @endif
                                @if($binLocation->shelf)
                                <span class="badge badge-info">Shelf: {{ $binLocation->shelf }}</span>
                                @endif
                                @if($binLocation->bin)
                                <span class="badge badge-info">Bin: {{ $binLocation->bin }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <strong>{{ $binLocation->capacity }}</strong>
                        </td>
                        <td class="text-right">
                            <strong style="color: {{ $isFull ? '#e74c3c' : ($isEmpty ? '#7f8c8d' : '#27ae60') }}">
                                {{ $binLocation->current_qty }}
                            </strong>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: #ecf0f1; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: {{ $barColor }}; height: 100%; width: {{ $capacityPercent }}%; transition: width 0.3s;"></div>
                                </div>
                                <span style="font-size: 11px; color: #7f8c8d; min-width: 35px;">{{ round($capacityPercent) }}%</span>
                            </div>
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $binLocation->id }})"
                                class="badge {{ $binLocation->is_active ? 'badge-success' : 'badge-warning' }}"
                                style="cursor: pointer; border: none;">
                                {{ $binLocation->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td>
                            <a href="#" wire:click.prevent="view({{ $binLocation->id }})" style="color: #3498db;">View</a>
                            @can('bin-locations.edit')
                            <a href="#" wire:click.prevent="edit({{ $binLocation->id }})" style="color: #27ae60; margin-left: 8px;">Edit</a>
                            @endcan
                            @can('bin-locations.delete')
                                @if($binLocation->current_qty == 0)
                                <a href="#" wire:click.prevent="confirmDelete({{ $binLocation->id }})" style="color: #e74c3c; margin-left: 8px;">Delete</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📍</div>
                            <p>No bin locations found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($binLocations->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $binLocations->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $binLocations->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $binLocations->total() }}</strong> results
                </div>
                <div>
                    {{ $binLocations->links() }}
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
                <span>{{ $binId ? 'Edit Bin Location' : 'Add New Bin Location' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;">
                            <label class="form-label">Warehouse <span style="color: #e74c3c;">*</span></label>
                            <select wire:model="warehouse_id" class="form-control">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehousesList as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Zone</label>
                            <input type="text" wire:model.live="zone" placeholder="A, B, C..." class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Aisle</label>
                            <input type="text" wire:model.live="aisle" placeholder="01, 02..." class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Rack</label>
                            <input type="text" wire:model.live="rack" placeholder="01, 02..." class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Shelf</label>
                            <input type="text" wire:model.live="shelf" placeholder="1, 2, 3..." class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Bin</label>
                            <input type="text" wire:model.live="bin" placeholder="1, 2, 3..." class="form-control">
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="form-label">Bin Code <span style="color: #e74c3c;">*</span></label>
                            <input type="text" wire:model="code" placeholder="Auto-generated or manual" class="form-control">
                            <div style="font-size: 10px; color: #7f8c8d; margin-top: 3px;">
                                Auto-generated from Zone-Aisle-Rack-Shelf-Bin if empty
                            </div>
                            @error('code') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="form-label">Capacity <span style="color: #e74c3c;">*</span></label>
                            <input type="number" wire:model="capacity" min="0" class="form-control">
                            @error('capacity') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
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
                                    <span>Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $binId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
    @php
        $binLocation = \App\Models\BinLocation::with(['warehouse'])->find($binId);
        $capacityPercent = $binLocation ? $this->getCapacityPercentage($binLocation) : 0;
    @endphp
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showViewModal', false)">
        <div style="background: #fff; width: 450px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Bin Location Details</span>
                <button wire:click="$set('showViewModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            @if($binLocation)
            <div style="padding: 20px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #ecf0f1; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">📍</div>
                    <h3 style="font-size: 18px; font-weight: bold;">{{ $binLocation->code }}</h3>
                    <span class="badge {{ $binLocation->is_active ? 'badge-success' : 'badge-warning' }}">
                        {{ $binLocation->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="grid-column: span 2;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Warehouse</div>
                        <div style="font-weight: bold;">{{ $binLocation->warehouse?->name ?? 'N/A' }}</div>
                    </div>

                    @if($binLocation->zone)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Zone</div>
                        <div style="font-weight: bold;">{{ $binLocation->zone }}</div>
                    </div>
                    @endif

                    @if($binLocation->aisle)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Aisle</div>
                        <div style="font-weight: bold;">{{ $binLocation->aisle }}</div>
                    </div>
                    @endif

                    @if($binLocation->rack)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Rack</div>
                        <div style="font-weight: bold;">{{ $binLocation->rack }}</div>
                    </div>
                    @endif

                    @if($binLocation->shelf)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Shelf</div>
                        <div style="font-weight: bold;">{{ $binLocation->shelf }}</div>
                    </div>
                    @endif

                    @if($binLocation->bin)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Bin</div>
                        <div style="font-weight: bold;">{{ $binLocation->bin }}</div>
                    </div>
                    @endif

                    <div style="grid-column: span 2; background: #f8f9fa; padding: 15px; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Capacity:</span>
                            <strong>{{ $binLocation->capacity }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Current Qty:</span>
                            <strong style="color: {{ $binLocation->current_qty >= $binLocation->capacity ? '#e74c3c' : '#27ae60' }}">{{ $binLocation->current_qty }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Available:</span>
                            <strong>{{ max(0, $binLocation->capacity - $binLocation->current_qty) }}</strong>
                        </div>

                        <div style="margin-top: 15px;">
                            <div style="background: #ecf0f1; height: 12px; border-radius: 6px; overflow: hidden;">
                                <div style="background: {{ $capacityPercent >= 100 ? '#e74c3c' : ($capacityPercent > 80 ? '#f39c12' : '#27ae60') }}; height: 100%; width: {{ $capacityPercent }}%;"></div>
                            </div>
                            <div style="text-align: center; font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                                {{ round($capacityPercent) }}% Full
                            </div>
                        </div>
                    </div>

                    @if($binLocation->notes)
                    <div style="grid-column: span 2; padding-top: 15px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Notes</div>
                        <div style="margin-top: 5px;">{{ $binLocation->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                <button wire:click="edit({{ $binId }})" class="btn btn-success">Edit</button>
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
                    <p>Are you sure you want to delete this bin location?</p>
                    <p style="color: #7f8c8d; font-size: 12px;">Only empty bin locations can be deleted.</p>
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
        $wire.on('bin-location-created', () => $wire.$refresh());
        $wire.on('bin-location-updated', () => $wire.$refresh());
        $wire.on('bin-location-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
