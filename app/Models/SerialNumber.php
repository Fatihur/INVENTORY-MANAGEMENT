<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SerialNumber extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'batch_id',
        'serial_number',
        'warehouse_id',
        'status',
        'sales_order_id',
        'manufacturing_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'warehouse_id', 'sales_order_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Serial Number {$eventName}");
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($serial) {
            if (!$serial->serial_number) {
                $prefix = 'SN';
                $lastSerial = static::where('serial_number', 'like', "{$prefix}%")
                    ->orderBy('serial_number', 'desc')
                    ->first();

                if ($lastSerial) {
                    $lastNumber = (int) substr($lastSerial->serial_number, strlen($prefix));
                    $serial->serial_number = $prefix . str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $serial->serial_number = $prefix . '00000001';
                }
            }
        });
    }
}
