<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'closed_by',
        'sent_at',
        'approved_at',
        'closed_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    public function canApprove(): bool
    {
        return $this->status === 'sent';
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['approved', 'partial']);
    }

    public function canClose(): bool
    {
        return $this->status === 'partial';
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(fn ($item) => $item->qty_received >= $item->qty_ordered);
    }

    /**
     * Get the remaining quantity to receive for an item.
     */
    public function getRemainingQty(int $itemId): int
    {
        $item = $this->items()->find($itemId);
        if (!$item) {
            return 0;
        }
        return max(0, $item->qty_ordered - $item->qty_received);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($po) {
            if (empty($po->po_number)) {
                $po->po_number = static::generateUniquePoNumber();
            }
        });
    }

    /**
     * Generate a unique PO number with race condition protection.
     */
    protected static function generateUniquePoNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'PO-' . $year . '-';

        // Use a transaction with lock to prevent duplicate numbers
        return \DB::transaction(function () use ($prefix, $year) {
            // Get the latest PO number for this year (including soft deleted)
            $latest = static::withTrashed()
                ->where('po_number', 'like', $prefix . '%')
                ->orderBy('po_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                // Extract the number part and increment
                $parts = explode('-', $latest->po_number);
                $lastNumber = intval(end($parts));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Double-check uniqueness (in case of race condition)
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $poNumber = $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
                $exists = static::withTrashed()->where('po_number', $poNumber)->exists();

                if (!$exists) {
                    return $poNumber;
                }

                $newNumber++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            // Fallback to timestamp-based number if all else fails
            return $prefix . str_pad(now()->format('Hisu'), 9, '0', STR_PAD_LEFT);
        });
    }
}
