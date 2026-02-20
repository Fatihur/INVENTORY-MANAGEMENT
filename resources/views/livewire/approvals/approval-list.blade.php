<div>
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">Approvals</h2>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
        <div class="panel" style="background: linear-gradient(135deg, #f39c12, #d68910); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 28px; font-weight: bold;">{{ $pendingCount }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Pending Approval</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #27ae60, #219a52); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 28px; font-weight: bold;">{{ $approvedCount }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Approved</div>
            </div>
        </div>
        <div class="panel" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="panel-body" style="padding: 15px;">
                <div style="font-size: 28px; font-weight: bold;">{{ $rejectedCount }}</div>
                <div style="font-size: 12px; opacity: 0.9;">Rejected</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div style="display: flex; gap: 5px; margin-bottom: 15px;">
        <button wire:click="setTab('pending')"
            style="padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-weight: {{ $activeTab === 'pending' ? 'bold' : 'normal' }}; background: {{ $activeTab === 'pending' ? '#3498db' : '#ecf0f1' }}; color: {{ $activeTab === 'pending' ? 'white' : '#2c3e50' }};">
            <i class="fas fa-clock"></i> Pending @if($pendingCount > 0)<span class="badge badge-warning" style="margin-left: 5px;">{{ $pendingCount }}</span>@endif
        </button>
        <button wire:click="setTab('approved')"
            style="padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-weight: {{ $activeTab === 'approved' ? 'bold' : 'normal' }}; background: {{ $activeTab === 'approved' ? '#27ae60' : '#ecf0f1' }}; color: {{ $activeTab === 'approved' ? 'white' : '#2c3e50' }};">
            <i class="fas fa-check-circle"></i> Approved
        </button>
        <button wire:click="setTab('rejected')"
            style="padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-weight: {{ $activeTab === 'rejected' ? 'bold' : 'normal' }}; background: {{ $activeTab === 'rejected' ? '#e74c3c' : '#ecf0f1' }}; color: {{ $activeTab === 'rejected' ? 'white' : '#2c3e50' }};">
            <i class="fas fa-times-circle"></i> Rejected
        </button>
    </div>

    <!-- Filters -->
    <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search requester..." class="form-control" style="width: 200px;">

            <select wire:model.live="typeFilter" class="form-control" style="width: 180px;">
                <option value="all">All Types</option>
                <option value="PurchaseOrder">Purchase Orders</option>
                <option value="StockOpname">Stock Opname</option>
                <option value="StockMovement">Stock Movement</option>
                <option value="SalesOrder">Sales Orders</option>
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
        <div class="panel-header">Approval Requests</div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="cursor: pointer;" wire:click="sort('created_at')">
                            Date
                            @if($sortBy === 'created_at'){{ $sortDirection === 'asc' ? ' &uarr;' : ' &darr;' }}@endif
                        </th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        @if($activeTab !== 'pending')
                        <th>Processed By</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvals as $approval)
                    @php
                        $documentType = $this->getDocumentType($approval);
                        $documentNumber = $this->getDocumentNumber($approval);
                        $details = $this->getDocumentDetails($approval);
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: bold;">{{ $approval->created_at->format('d M Y') }}</div>
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $approval->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <div style="font-weight: bold; color: #3498db;">{{ $documentNumber }}</div>
                            @if(count($details) > 0)
                            <div style="font-size: 10px; color: #7f8c8d;">
                                @foreach($details as $key => $value)
                                    {{ $key }}: {{ $value }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $documentType }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $approval->requester?->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'pending' => ['class' => 'badge-warning', 'icon' => '<i class="fas fa-clock"></i>'],
                                    'approved' => ['class' => 'badge-success', 'icon' => '<i class="fas fa-check-circle"></i>'],
                                    'rejected' => ['class' => 'badge-danger', 'icon' => '<i class="fas fa-times-circle"></i>'],
                                ];
                                $config = $statusConfig[$approval->status] ?? $statusConfig['pending'];
                            @endphp
                            <span class="badge {{ $config['class'] }}">
                                {{ $config['icon'] }} {{ ucfirst($approval->status) }}
                            </span>
                        </td>
                        @if($activeTab !== 'pending')
                        <td>
                            <div style="font-weight: bold;">{{ $approval->approver?->name ?? 'N/A' }}</div>
                            @if($approval->approved_at)
                            <div style="font-size: 10px; color: #7f8c8d;">{{ $approval->approved_at->format('d M Y H:i') }}</div>
                            @endif
                        </td>
                        @endif
                        <td>
                            <a href="#" wire:click.prevent="view({{ $approval->id }})" style="color: #3498db;">View</a>
                            @if($approval->status === 'pending')
                                @can('approvals.approve')
                                <a href="#" wire:click.prevent="confirmApprove({{ $approval->id }})" style="color: #27ae60; margin-left: 8px;">Approve</a>
                                @endcan
                                @can('approvals.reject')
                                <a href="#" wire:click.prevent="confirmReject({{ $approval->id }})" style="color: #e74c3c; margin-left: 8px;">Reject</a>
                                @endcan
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $activeTab === 'pending' ? 6 : 7 }}" style="text-align: center; color: #7f8c8d; padding: 40px;">
                            <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-clipboard-list" style="color: #bdc3c7;"></i></div>
                            <p>No {{ $activeTab }} approvals found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($approvals->hasPages())
        <div style="padding: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="color: #7f8c8d; font-size: 11px;">
                    Showing <strong>{{ $approvals->firstItem() ?? 0 }}</strong>
                    to <strong>{{ $approvals->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $approvals->total() }}</strong> results
                </div>
                <div>
                    {{ $approvals->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- View Modal -->
    @if($showViewModal)
    @php
        $approval = \App\Models\ApprovalRequest::with(['requester', 'approver', 'approvable'])->find($approvalId);
        $documentType = $approval ? $this->getDocumentType($approval) : '';
        $documentNumber = $approval ? $this->getDocumentNumber($approval) : '';
        $details = $approval ? $this->getDocumentDetails($approval) : [];
    @endphp
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showViewModal', false)">
        <div style="background: #fff; width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Approval Details</span>
                <button wire:click="$set('showViewModal', false)" style="background: none; border: none; cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>
            </div>

            @if($approval)
            <div style="padding: 20px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #ecf0f1; margin-bottom: 20px;">
                    <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-clipboard-list" style="color: #3498db;"></i></div>
                    <h3 style="font-size: 16px; font-weight: bold;">{{ $documentNumber }}</h3>
                    <span class="badge badge-info">{{ $documentType }}</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Status</div>
                        @php
                            $statusConfig = [
                                'pending' => ['class' => 'badge-warning'],
                                'approved' => ['class' => 'badge-success'],
                                'rejected' => ['class' => 'badge-danger'],
                            ];
                            $config = $statusConfig[$approval->status] ?? $statusConfig['pending'];
                        @endphp
                        <span class="badge {{ $config['class'] }}">{{ ucfirst($approval->status) }}</span>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Requested By</div>
                        <div style="font-weight: bold;">{{ $approval->requester?->name ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Request Date</div>
                        <div style="font-weight: bold;">{{ $approval->created_at->format('d M Y H:i') }}</div>
                    </div>

                    @if($approval->approved_by)
                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Processed By</div>
                        <div style="font-weight: bold;">{{ $approval->approver?->name ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Processed Date</div>
                        <div style="font-weight: bold;">{{ $approval->approved_at?->format('d M Y H:i') ?? 'N/A' }}</div>
                    </div>
                    @endif

                    @if(count($details) > 0)
                    <div style="grid-column: span 2; padding-top: 15px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 10px;">Document Details</div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                            @foreach($details as $key => $value)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: #7f8c8d;">{{ $key }}</span>
                                <span style="font-weight: bold;">{{ $value }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($approval->comments)
                    <div style="grid-column: span 2; padding-top: 15px; border-top: 1px solid #ecf0f1;">
                        <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">Comments</div>
                        <div style="margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 4px;">{{ $approval->comments }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                @if($approval->status === 'pending')
                <button wire:click="confirmReject({{ $approvalId }})" class="btn btn-danger">Reject</button>
                <button wire:click="confirmApprove({{ $approvalId }})" class="btn btn-success">Approve</button>
                @endif
                <button wire:click="$set('showViewModal', false)" class="btn btn-default">Close</button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Approve Modal -->
    @if($showApproveModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showApproveModal', false)">
        <div style="background: #fff; width: 450px; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="background: #27ae60; color: white;">Approve Request</div>
            <form wire:submit="approve">
                <div style="padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: #27ae60;"></i></div>
                        <p>Are you sure you want to approve this request?</p>
                    </div>

                    <div>
                        <label class="form-label">Comments (Optional)</label>
                        <textarea wire:model="comments" rows="3" class="form-control" placeholder="Add any comments..."></textarea>
                        @error('comments') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showApproveModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;"
         wire:click.self="$set('showRejectModal', false)">
        <div style="background: #fff; width: 450px; border: 1px solid #bdc3c7; border-radius: 4px;">
            <div class="panel-header" style="background: #e74c3c; color: white;">Reject Request</div>
            <form wire:submit="reject">
                <div style="padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 48px; margin-bottom: 10px;"><i class="fas fa-times-circle" style="color: #e74c3c;"></i></div>
                        <p>Are you sure you want to reject this request?</p>
                    </div>

                    <div>
                        <label class="form-label">Reason for Rejection <span style="color: #e74c3c;">*</span></label>
                        <textarea wire:model="comments" rows="3" class="form-control" placeholder="Provide reason for rejection..." required></textarea>
                        @error('comments') <div style="color: #e74c3c; font-size: 10px; margin-top: 3px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="padding: 15px 20px; border-top: 1px solid #ecf0f1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="$set('showRejectModal', false)" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @script
    <script>
        $wire.on('approval-approved', () => $wire.$refresh());
        $wire.on('approval-rejected', () => $wire.$refresh());
    </script>
    @endscript
</div>
