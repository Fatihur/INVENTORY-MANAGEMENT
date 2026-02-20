<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ApprovalLevel extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'model_type',
        'level_order',
        'conditions',
        'role_id',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'level_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'level_order', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Approval Level {$eventName}");
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public static function getNextLevel(string $modelType, int $currentLevel = 0): ?self
    {
        return static::where('model_type', $modelType)
            ->where('level_order', '>', $currentLevel)
            ->where('is_active', true)
            ->orderBy('level_order')
            ->first();
    }
}
