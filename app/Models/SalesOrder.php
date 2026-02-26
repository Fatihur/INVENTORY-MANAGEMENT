<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalesOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'so_number',
        'customer_id',
        'warehouse_id',
        'status',
        'order_date',
        'delivery_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'confirmed_at',
        'shipped_at',
        'tracking_number',
        'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_amount', 'approved_by'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Sales Order {$eventName}");
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalRequests()
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (! $order->so_number) {
                $order->so_number = static::generateUniqueSoNumber();
            }
        });
    }

    /**
     * Generate a unique SO number with race condition protection.
     */
    protected static function generateUniqueSoNumber(): string
    {
        $prefix = config('inventory.prefixes.sales_order', 'SO');
        $year = now()->format('Y');
        $prefixWithYear = "{$prefix}-{$year}-";

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefixWithYear) {
            // Get the latest SO number for this year (including soft deleted)
            $latest = static::withTrashed()
                ->where('so_number', 'like', "{$prefixWithYear}%")
                ->orderBy('so_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $lastNumber = (int) substr($latest->so_number, -6);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $soNumber = $prefixWithYear.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
                $exists = static::withTrashed()->where('so_number', $soNumber)->exists();

                if (! $exists) {
                    return $soNumber;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefixWithYear.str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->tax_amount = $this->items->sum(function ($item) {
            return $item->subtotal * ($item->tax_rate / 100);
        });
        $this->discount_amount = $this->items->sum(function ($item) {
            return $item->subtotal * ($item->discount_percent / 100);
        });
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }
}
