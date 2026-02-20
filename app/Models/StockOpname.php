<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'actual_qty' => 'integer',
        'variance_qty' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'variance_qty', 'approved_by'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Stock Opname {$eventName}");
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opname) {
            if (!$opname->opname_number) {
                $prefix = 'OP';
                $year = date('Y');
                $lastOpname = static::where('opname_number', 'like', "{$prefix}-{$year}%")
                    ->orderBy('opname_number', 'desc')
                    ->first();

                if ($lastOpname) {
                    $lastNumber = (int) substr($lastOpname->opname_number, -6);
                    $opname->opname_number = $prefix . '-' . $year . '-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $opname->opname_number = $prefix . '-' . $year . '-000001';
                }
            }

            $opname->variance_qty = $opname->actual_qty - $opname->system_qty;
        });

        static::updating(function ($opname) {
            $opname->variance_qty = $opname->actual_qty - $opname->system_qty;
        });
    }
}
