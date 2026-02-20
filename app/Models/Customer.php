<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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
            ->setDescriptionForEvent(fn(string $eventName) => "Customer {$eventName}");
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
            if (!$customer->code) {
                $prefix = 'CUST';
                $lastCustomer = static::where('code', 'like', "{$prefix}%")
                    ->orderBy('code', 'desc')
                    ->first();

                if ($lastCustomer) {
                    $lastNumber = (int) substr($lastCustomer->code, strlen($prefix));
                    $customer->code = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                } else {
                    $customer->code = $prefix . '00001';
                }
            }
        });
    }
}
