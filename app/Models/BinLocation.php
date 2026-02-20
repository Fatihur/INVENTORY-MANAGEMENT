<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BinLocation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'warehouse_id',
        'code',
        'zone',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'capacity',
        'current_qty',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_qty' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['current_qty', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Bin Location {$eventName}");
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function getAvailableCapacityAttribute(): int
    {
        return $this->capacity - $this->current_qty;
    }

    public function isFull(): bool
    {
        return $this->current_qty >= $this->capacity;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bin) {
            if (!$bin->code) {
                $prefix = 'BIN';
                $lastBin = static::where('code', 'like', "{$prefix}%")
                    ->orderBy('code', 'desc')
                    ->first();

                if ($lastBin) {
                    $lastNumber = (int) substr($lastBin->code, strlen($prefix));
                    $bin->code = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                } else {
                    $bin->code = $prefix . '00001';
                }
            }
        });
    }
}
