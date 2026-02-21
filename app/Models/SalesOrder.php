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
                $prefix = 'SO';
                $year = date('Y');
                $lastOrder = static::where('so_number', 'like', "{$prefix}-{$year}%")
                    ->orderBy('so_number', 'desc')
                    ->first();

                if ($lastOrder) {
                    $lastNumber = (int) substr($lastOrder->so_number, -6);
                    $order->so_number = $prefix.'-'.$year.'-'.str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $order->so_number = $prefix.'-'.$year.'-000001';
                }
            }
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
