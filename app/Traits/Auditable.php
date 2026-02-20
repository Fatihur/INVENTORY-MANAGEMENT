<?php

namespace App\Traits;

use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\Models\Activity;

trait Auditable
{
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function logActivity(string $description, array $properties = [])
    {
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($description);
    }

    public function getAuditTrail()
    {
        return $this->activities()
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer' => $activity->causer?->name ?? 'System',
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });
    }
}
