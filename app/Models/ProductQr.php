<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQr extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'qr_code_value',
        'type',
        'batch_number',
        'expiry_date',
        'warehouse_id',
        'printed_at',
        'print_count',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'printed_at' => 'datetime',
        'print_count' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($qr) {
            if (empty($qr->qr_code_value)) {
                $qr->qr_code_value = match ($qr->type) {
                    'product' => 'PROD:' . $qr->product_id,
                    'batch' => 'BATCH:' . $qr->product_id . ':' . $qr->batch_number,
                    'location' => 'LOC:' . $qr->warehouse_id,
                    default => 'QR:' . uniqid(),
                };
            }
        });
    }

    public function markAsPrinted(): void
    {
        $this->update([
            'printed_at' => now(),
            'print_count' => $this->print_count + 1,
        ]);
    }
}
