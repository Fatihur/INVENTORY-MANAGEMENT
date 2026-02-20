<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Batch extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'initial_qty',
        'remaining_qty',
        'warehouse_id',
        'cost_price',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'initial_qty' => 'integer',
        'remaining_qty' => 'integer',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['remaining_qty', 'is_active', 'expiry_date'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Batch {$eventName}");
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->diffInDays(now(), false) <= $days;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (!$batch->batch_number) {
                $prefix = 'BATCH';
                $lastBatch = static::where('batch_number', 'like', "{$prefix}%")
                    ->orderBy('batch_number', 'desc')
                    ->first();

                if ($lastBatch) {
                    $lastNumber = (int) substr($lastBatch->batch_number, strlen($prefix));
                    $batch->batch_number = $prefix . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $batch->batch_number = $prefix . '000001';
                }
            }
        });
    }
}
