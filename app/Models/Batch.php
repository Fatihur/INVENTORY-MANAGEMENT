<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
            ->setDescriptionForEvent(fn (string $eventName) => "Batch {$eventName}");
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
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now(), false) <= $days;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (! $batch->batch_number) {
                $batch->batch_number = static::generateUniqueBatchNumber();
            }
        });
    }

    /**
     * Generate a unique batch number with race condition protection.
     */
    protected static function generateUniqueBatchNumber(): string
    {
        $prefix = config('inventory.prefixes.batch', 'BATCH');

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefix) {
            // Get the latest batch number
            $latest = static::where('batch_number', 'like', "{$prefix}%")
                ->orderBy('batch_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $lastNumber = (int) substr($latest->batch_number, strlen($prefix));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $batchNumber = $prefix.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
                $exists = static::where('batch_number', $batchNumber)->exists();

                if (! $exists) {
                    return $batchNumber;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefix.str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }
}
