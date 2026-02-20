<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty_on_hand',
        'qty_reserved',
        'avg_cost',
        'last_movement_at',
    ];

    protected $casts = [
        'qty_on_hand' => 'integer',
        'qty_reserved' => 'integer',
        'avg_cost' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->qty_on_hand <= $this->product->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->qty_on_hand <= 0;
    }
}
