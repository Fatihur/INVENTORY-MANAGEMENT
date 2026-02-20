<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'qty_ordered',
        'qty_received',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'qty_ordered' => 'integer',
        'qty_received' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function getRemainingQtyAttribute(): int
    {
        return $this->qty_ordered - $this->qty_received;
    }
}
