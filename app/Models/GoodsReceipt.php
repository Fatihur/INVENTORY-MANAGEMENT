<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'received_date',
        'invoice_number',
        'invoice_date',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'invoice_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gr) {
            if (empty($gr->gr_number)) {
                $gr->gr_number = static::generateUniqueGrNumber();
            }
        });
    }

    /**
     * Generate a unique GR number with race condition protection.
     */
    protected static function generateUniqueGrNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'GR-'.$year.'-';

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefix) {
            // Get the latest GR number for this year (including soft deleted)
            $latest = static::withTrashed()
                ->where('gr_number', 'like', $prefix.'%')
                ->orderBy('gr_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $parts = explode('-', $latest->gr_number);
                $lastNumber = intval(end($parts));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $grNumber = $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
                $exists = static::withTrashed()->where('gr_number', $grNumber)->exists();

                if (! $exists) {
                    return $grNumber;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefix.str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }
}
