<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'tax_id',
        'credit_limit',
        'payment_terms',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'credit_limit', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Customer {$eventName}");
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('type', 'billing')->where('is_default', true);
    }

    public function shippingAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('type', 'shipping')->where('is_default', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (! $customer->code) {
                $customer->code = static::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique customer code with race condition protection.
     */
    protected static function generateUniqueCode(): string
    {
        $prefix = config('inventory.prefixes.customer', 'CUST');

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefix) {
            // Get the latest customer code (including soft deleted)
            $latest = static::withTrashed()
                ->where('code', 'like', "{$prefix}%")
                ->orderBy('code', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $lastNumber = (int) substr($latest->code, strlen($prefix));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $code = $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
                $exists = static::withTrashed()->where('code', $code)->exists();

                if (! $exists) {
                    return $code;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefix.str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }
}
