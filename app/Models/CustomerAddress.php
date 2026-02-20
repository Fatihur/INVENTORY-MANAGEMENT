<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CustomerAddress extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id',
        'type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['address_line1', 'city', 'postal_code', 'is_default'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer address {$eventName}");
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
