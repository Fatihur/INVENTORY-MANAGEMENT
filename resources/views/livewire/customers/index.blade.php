<div>
    <!-- Page Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Customers</h2>
        @can('customers.create')
        <button wire:click="create" class="btn btn-primary">+ Add Customer</button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body filter-row" style="display: flex; gap: 10px; flex-wrap: wrap; border-bottom: 1px solid #bdc3c7;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, code, email..." class="form-control" style="flex: 1; min-width: 150px;">
            <select wire:model.live="status" class="form-control" style="flex: 0 0 auto; min-width: 120px;">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select wire:model.live="perPage" class="form-control" style="flex: 0 0 auto; min-width: 120px;">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
            </select>
            <button wire:click="$refresh" class="btn btn-default">Refresh</button>
        </div>
    </div>

    <!-- Table -->
    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('code')">
                            Code
                            @if($sortBy === 'code'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th style="cursor: pointer;" wire:click="sort('name')">
                            Customer Name
                            @if($sortBy === 'name'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Contact</th>
                        <th class="text-right">Credit Limit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <span style="font-weight: bold; color: #3498db;">{{ $customer->code }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $customer->name }}</div>
                            @if($customer->tax_id)
                            <div style="color: #7f8c8d; font-size: 10px;">Tax ID: {{ $customer->tax_id }}</div>
                            @endif
                        </td>
                        <td>
                            @if($customer->email)
                            <div style="font-size: 11px;"><i class="fas fa-envelope" style="color: #3498db;"></i> {{ $customer->email }}</div>
                            @endif
                            @if($customer->phone)
                            <div style="font-size: 11px;"><i class="fas fa-phone" style="color: #27ae60;"></i> {{ $customer->phone }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</strong>
                            @if($customer->payment_terms > 0)
                            <div style="color: #7f8c8d; font-size: 10px;">{{ $customer->payment_terms }} days</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-warning' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="#" wire:click.prevent="view({{ $customer->id }})" style="color: #3498db;">View</a>
                            @can('customers.edit')
                            <a href="#" wire:click.prevent="edit({{ $customer->id }})" style="color: #27ae60; margin-left: 10px;">Edit</a>
                            @endcan
                            @can('customers.delete')
                            <a href="#" wire:click.prevent="delete({{ $customer->id }})" style="color: #e74c3c; margin-left: 10px;"
                                onclick="confirm('Are you sure you want to delete this customer?') || event.stopImmediatePropagation()">Delete</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d;">No customers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $customers->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $customers->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $customers->total() }}</strong> results
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div class="modal-overlay"
         wire:click.self="$set('showModal', false)">
        <div class="modal-content" style="background: #fff; width: 700px; max-width: 90vw; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $customerId ? 'Edit Customer' : 'Add New Customer' }}</span>
                <button wire:click="$set('showModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            <form wire:submit="save">
                <div style="padding: 15px;">
                    <div class="modal-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;" class="modal-full-width">
                            <label class="form-label">Customer Name <span style="color: #e74c3c;">*</span></label>
                            <input type="text" wire:model="name" placeholder="Enter customer name" class="form-control" autofocus>
                            @error('name') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" wire:model="email" placeholder="customer@email.com" class="form-control">
                            @error('email') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Phone Number</label>
                            <input type="text" wire:model="phone" placeholder="+62 xxx-xxxx-xxxx" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Tax ID (NPWP)</label>
                            <input type="text" wire:model="tax_id" placeholder="xx.xxx.xxx.x-xxx.xxx" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Payment Terms (Days)</label>
                            <input type="number" wire:model="payment_terms" placeholder="0" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Credit Limit (Rp)</label>
                            <input type="number" step="0.01" wire:model="credit_limit" placeholder="0" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Status</label>
                            <div style="margin-top: 5px;">
                                <label style="cursor: pointer;">
                                    <input type="checkbox" wire:model="is_active">
                                    <span style="margin-left: 5px;">Active Customer</span>
                                </label>
                            </div>
                        </div>

                        <div style="grid-column: span 2;" class="modal-full-width">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" rows="3" placeholder="Additional notes..." class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div style="padding: 10px 15px; border-top: 1px solid #bdc3c7; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $customerId ? 'Update Customer' : 'Create Customer' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
    <div class="modal-overlay"
         wire:click.self="$set('showViewModal', false)">
        <div class="modal-content" style="background: #fff; width: 600px; max-width: 90vw; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7;">
            <div class="panel-header">Customer Details</div>

            <div style="padding: 15px;">
                <div style="text-align: center; padding-bottom: 15px; border-bottom: 1px solid #ecf0f1; margin-bottom: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #3498db; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; margin: 0 auto 10px;">
                        {{ substr($name, 0, 1) }}
                    </div>
                    <h3 style="font-size: 14px; font-weight: bold;">{{ $name }}</h3>
                    <p style="color: #7f8c8d; font-size: 11px;">{{ $customer->code ?? 'CUST-XXX' }}</p>
                </div>

                <div class="modal-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Email Address</div>
                        <div style="font-weight: bold;">{{ $email ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Phone Number</div>
                        <div style="font-weight: bold;">{{ $phone ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Tax ID</div>
                        <div style="font-weight: bold;">{{ $tax_id ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Status</div>
                        <span class="badge {{ $is_active ? 'badge-success' : 'badge-warning' }}">
                            {{ $is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Credit Limit</div>
                        <div style="font-weight: bold; color: #3498db;">Rp {{ number_format($credit_limit, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 3px;">Payment Terms</div>
                        <div style="font-weight: bold;">{{ $payment_terms }} days</div>
                    </div>

                    @if($notes)
                    <div style="grid-column: span 2; padding-top: 10px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 5px;">Notes</div>
                        <p>{{ $notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div style="padding: 10px 15px; border-top: 1px solid #bdc3c7; display: flex; justify-content: flex-end; gap: 10px;">
                <button wire:click="edit({{ $customerId }})" class="btn btn-success">Edit</button>
                <button wire:click="$set('showViewModal', false)" class="btn btn-default">Close</button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        $wire.on('customer-created', () => $wire.$refresh());
        $wire.on('customer-updated', () => $wire.$refresh());
        $wire.on('customer-deleted', () => $wire.$refresh());
    </script>
    @endscript
</div>
