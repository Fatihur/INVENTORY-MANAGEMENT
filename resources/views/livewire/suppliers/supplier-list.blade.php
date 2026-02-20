<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Suppliers</h2>
        @can('suppliers.create')
            <a wire:navigate href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Add Supplier</a>
        @endcan
    </div>

    <div class="panel">
        <div style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Lead Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->code }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                        <td>{{ $supplier->default_lead_time_days }} days</td>
                        <td>
                            <a wire:navigate href="{{ route('suppliers.show', $supplier) }}" style="color: #3498db;">View</a>
                            @can('suppliers.edit')
                                <a wire:navigate href="{{ route('suppliers.edit', $supplier) }}" style="color: #27ae60; margin-left: 10px;">Edit</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #7f8c8d;">No suppliers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">{{ $suppliers->links() }}</div>
    </div>
</div>
