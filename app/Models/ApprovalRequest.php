<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ApprovalRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'approval_level_id',
        'requested_by',
        'approved_by',
        'status',
        'comments',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'approved_by', 'comments'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Approval Request {$eventName}");
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function approvalLevel()
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approve(?User $user, ?string $comments = null)
    {
        $this->update([
            'approved_by' => $user?->id,
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now(),
        ]);
    }

    public function reject(?User $user, ?string $comments = null)
    {
        $this->update([
            'approved_by' => $user?->id,
            'status' => 'rejected',
            'comments' => $comments,
        ]);
    }
}
