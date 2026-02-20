<div>
    <!-- Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">
            <i class="fas fa-box"></i> Products
        </h2>
        @can('products.create')
            <a wire:navigate href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="panel-body" style="padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold;">{{ $totalProducts ?? 0 }}</div>
                <div style="font-size: 11px; opacity: 0.9;"><i class="fas fa-box"></i> Total Products</div>
            </div>
        </div>
        <div class="panel stat-card" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold;">{{ $activeProducts ?? 0 }}</div>
                <div style="font-size: 11px; opacity: 0.9;"><i class="fas fa-check-circle"></i> Active</div>
            </div>
        </div>
        <div class="panel stat-card" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
            <div class="panel-body" style="padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold;">{{ $lowStockCount ?? 0 }}</div>
                <div style="font-size: 11px; opacity: 0.9;"><i class="fas fa-exclamation-triangle"></i> Low Stock</div>
            </div>
        </div>
        <div class="panel stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="panel-body" style="padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold;">{{ $outOfStockCount ?? 0 }}</div>
                <div style="font-size: 11px; opacity: 0.9;"><i class="fas fa-times-circle"></i> Out of Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body">
            <div class="filter-row" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                <!-- Search with Clear Button -->
                <div style="position: relative; flex: 1; min-width: 200px;">
                    <label class="form-label" style="font-size: 11px; color: #7f8c8d;">
                        <i class="fas fa-search"></i> Search
                    </label>
                    <div class="input-group">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               id="productSearch"
                               placeholder="Search products..."
                               class="form-control"
                               style="padding-right: 30px;">
                        @if($search)
                            <button class="input-clear" wire:click="$set('search', '')" title="Clear search">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Status Filter -->
                <div style="flex: 0 0 auto;">
                    <label class="form-label" style="font-size: 11px; color: #7f8c8d;">
                        <i class="fas fa-filter"></i> Status
                    </label>
                    <select wire:model.live="status" class="form-control" style="width: 150px; min-width: 150px;">
                        <option value="">All Status</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div style="flex: 0 0 auto;">
                    <label class="form-label" style="font-size: 11px; color: #7f8c8d;">
                        <i class="fas fa-list-ol"></i> Show
                    </label>
                    <select wire:model.live="perPage" class="form-control" style="width: 120px; min-width: 120px;">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>

                <!-- Refresh Button -->
                <button wire:click="$refresh" class="btn btn-default" wire:loading.attr="disabled">
                    <i class="fas fa-sync-alt" wire:loading.class="fa-spin" wire:target="$refresh"></i>
                    Refresh
                </button>
            </div>

            <!-- Active Filter Badges -->
            @if($search || $status)
                <div class="filter-badges">
                    <span style="font-size: 11px; color: #7f8c8d; margin-right: 5px;">Active Filters:</span>
                    @if($search)
                        <span class="filter-badge">
                            Search: {{ Str::limit($search, 20) }}
                            <i class="fas fa-times remove" wire:click="$set('search', '')"></i>
                        </span>
                    @endif
                    @if($status)
                        <span class="filter-badge">
                            Status: {{ ucfirst($status) }}
                            <i class="fas fa-times remove" wire:click="$set('status', '')"></i>
                        </span>
                    @endif
                    <a href="javascript:void(0)" wire:click="$set('search', ''); $set('status', '')" style="font-size: 11px; color: #e74c3c; margin-left: 10px;">
                        <i class="fas fa-times-circle"></i> Clear All
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Products Table -->
    <div class="panel">
        <div class="panel-header">
            <span><i class="fas fa-list"></i> Product List</span>
            <span wire:loading class="badge badge-info">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </span>
        </div>

        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">SKU</th>
                        <th>Product Name</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Min Stock</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: bold; color: #3498db;">
                                    {{ $product->sku }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $product->name }}</div>
                                @if($product->category)
                                    <small style="color: #7f8c8d;">
                                        <i class="fas fa-folder"></i> {{ $product->category->name }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-right">
                                <span style="font-weight: bold; font-size: 13px;">
                                    {{ $product->total_stock }}
                                </span>
                            </td>
                            <td class="text-right" style="color: #7f8c8d;">
                                {{ $product->min_stock }}
                            </td>
                            <td>
                                @if($product->total_stock <= 0)
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i> Out of Stock
                                    </span>
                                @elseif($product->total_stock <= $product->min_stock)
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Low Stock
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> In Stock
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a wire:navigate href="{{ route('products.show', $product) }}" class="action-link" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('products.edit')
                                    <a wire:navigate href="{{ route('products.edit', $product) }}" class="action-link success ml-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('products.delete')
                                    <button wire:click="confirmDelete({{ $product->id }})" class="action-link danger ml-2" style="background: none; border: none; cursor: pointer;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <h3 style="font-size: 14px; margin-bottom: 8px;">No Products Found</h3>
                                    <p style="margin-bottom: 15px;">
                                        @if($search || $status)
                                            No products match your search criteria. Try adjusting your filters.
                                        @else
                                            There are no products in the system yet.
                                        @endif
                                    </p>
                                    @if($search || $status)
                                        <button wire:click="$set('search', ''); $set('status', '')" class="btn btn-default">
                                            <i class="fas fa-times-circle"></i> Clear Filters
                                        </button>
                                    @else
                                        @can('products.create')
                                            <a wire:navigate href="{{ route('products.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add First Product
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal-overlay" wire:click.self="$set('showDeleteModal', false)">
            <div class="modal-content" style="width: 400px;">
                <div class="panel-header" style="background: #e74c3c; color: white;">
                    <span><i class="fas fa-exclamation-triangle"></i> Confirm Delete</span>
                    <button wire:click="$set('showDeleteModal', false)" style="background: none; border: none; color: white; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="padding: 20px; text-align: center;">
                    <div style="font-size: 48px; color: #e74c3c; margin-bottom: 15px;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <p style="margin-bottom: 20px;">
                        Are you sure you want to delete <strong>{{ $deleteProductName }}</strong>?
                    </p>
                    <p style="color: #7f8c8d; font-size: 11px; margin-bottom: 20px;">
                        This action cannot be undone. All associated stock records will also be deleted.
                    </p>
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button wire:click="$set('showDeleteModal', false)" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button wire:click="delete" class="btn btn-danger" wire:loading.attr="disabled">
                            <i class="fas fa-trash" wire:loading.class="fa-spinner fa-spin" wire:target="delete"></i>
                            <span wire:loading.remove wire:target="delete">Delete</span>
                            <span wire:loading wire:target="delete">Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
