<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalesOrderItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_percent',
        'subtotal',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quantity', 'unit_price', 'total'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Sales Order Item {$eventName}");
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
            $item->total = $item->subtotal - ($item->subtotal * $item->discount_percent / 100);
        });
    }
}
