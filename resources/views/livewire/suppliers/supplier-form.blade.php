<div>
    <h2 style="font-size: 14px; font-weight: bold; margin-bottom: 15px;">{{ $supplier ? 'Edit Supplier' : 'Add Supplier' }}</h2>

    <form wire:submit="save">
        <div class="panel">
            <div class="panel-header">Supplier Information</div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" wire:model="code" class="form-control">
                        @error('code') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" wire:model="name" class="form-control">
                        @error('name') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" wire:model="contact_person" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lead Time (Days)</label>
                        <input type="number" wire:model="default_lead_time_days" min="1" class="form-control">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Address</label>
                        <textarea wire:model="address" rows="3" class="form-control"></textarea>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" wire:model="is_active" style="margin-right: 5px;">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">{{ $supplier ? 'Update' : 'Create' }} Supplier</button>
            <a wire:navigate href="{{ route('suppliers.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
