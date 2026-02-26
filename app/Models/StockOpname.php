<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockOpname extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'opname_number',
        'warehouse_id',
        'product_id',
        'batch_id',
        'bin_location_id',
        'system_qty',
        'actual_qty',
        'variance_qty',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'completed_at',
        'completed_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'actual_qty' => 'integer',
        'variance_qty' => 'integer',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'variance_qty', 'approved_by'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Stock Opname {$eventName}");
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opname) {
            if (! $opname->opname_number) {
                $opname->opname_number = static::generateUniqueOpnameNumber();
            }

            $opname->variance_qty = $opname->actual_qty - $opname->system_qty;
        });

        static::updating(function ($opname) {
            $opname->variance_qty = $opname->actual_qty - $opname->system_qty;
        });
    }

    /**
     * Generate a unique opname number with race condition protection.
     */
    protected static function generateUniqueOpnameNumber(): string
    {
        $prefix = config('inventory.prefixes.stock_opname', 'OP');
        $year = now()->format('Y');
        $prefixWithYear = "{$prefix}-{$year}-";

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefixWithYear) {
            // Get the latest opname number for this year
            $latest = static::where('opname_number', 'like', "{$prefixWithYear}%")
                ->orderBy('opname_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $lastNumber = (int) substr($latest->opname_number, -6);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $opnameNumber = $prefixWithYear.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
                $exists = static::where('opname_number', $opnameNumber)->exists();

                if (! $exists) {
                    return $opnameNumber;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefixWithYear.str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }
}
