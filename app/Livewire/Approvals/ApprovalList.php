<?php

namespace App\Livewire\Approvals;

use App\Models\ApprovalRequest;
use App\Models\ApprovalLevel;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ApprovalList extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'pending';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $typeFilter = 'all';

    public $showViewModal = false;
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $approvalId = null;
    public $comments = '';

    public function render()
    {
        $query = ApprovalRequest::with(['approvable', 'requester', 'approvalLevel']);

        if ($this->search) {
            $query->whereHas('requester', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }

        // Calculate counts for stats cards
        $pendingCount = ApprovalRequest::where('status', 'pending')->count();
        $approvedCount = ApprovalRequest::where('status', 'approved')->count();
        $rejectedCount = ApprovalRequest::where('status', 'rejected')->count();

        $approvals = $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.approvals.approval-list', [
            'approvals' => $approvals,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ])->title('Approvals - Inventory Management');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function view($id)
    {
        $this->approvalId = $id;
        $this->showViewModal = true;
    }

    public function approve($id)
    {
        $this->approvalId = $id;
        $this->comments = '';
        $this->showApproveModal = true;
    }

    public function reject($id)
    {
        $this->approvalId = $id;
        $this->comments = '';
        $this->showRejectModal = true;
    }

    public function confirmApprove()
    {
        $approval = ApprovalRequest::find($this->approvalId);
        if ($approval && $approval->status === 'pending') {
            DB::transaction(function () use ($approval) {
                $approval->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'comments' => $this->comments,
                ]);

                // Update the approvable model status if needed
                if ($approval->approvable && method_exists($approval->approvable, 'onApproved')) {
                    $approval->approvable->onApproved();
                }
            });

            $this->dispatch('approval-approved');
        }

        $this->showApproveModal = false;
        $this->reset(['approvalId', 'comments']);
    }

    public function confirmReject()
    {
        $approval = ApprovalRequest::find($this->approvalId);
        if ($approval && $approval->status === 'pending') {
            DB::transaction(function () use ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'comments' => $this->comments,
                ]);

                if ($approval->approvable && method_exists($approval->approvable, 'onRejected')) {
                    $approval->approvable->onRejected();
                }
            });

            $this->dispatch('approval-rejected');
        }

        $this->showRejectModal = false;
        $this->reset(['approvalId', 'comments']);
    }

    public function delete($id)
    {
        $approval = ApprovalRequest::find($id);
        if ($approval) {
            $approval->delete();
            $this->dispatch('approval-deleted');
        }
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedActiveTab()
    {
        $this->resetPage();
    }
}
