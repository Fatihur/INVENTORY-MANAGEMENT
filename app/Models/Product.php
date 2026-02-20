<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit',
        'category',
        'min_stock',
        'safety_stock',
        'target_stock',
        'lead_time_days',
        'track_batch',
        'is_active',
    ];

    protected $casts = [
        'min_stock' => 'integer',
        'safety_stock' => 'integer',
        'target_stock' => 'integer',
        'lead_time_days' => 'integer',
        'track_batch' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['total_stock', 'available_stock'];

    public function qrCodes(): HasMany
    {
        return $this->hasMany(ProductQr::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_products')
            ->withPivot(['is_primary', 'buy_price', 'moq', 'lead_time_days_override', 'supplier_sku'])
            ->withTimestamps();
    }

    public function primarySupplier(): ?Supplier
    {
        return $this->suppliers()
            ->wherePivot('is_primary', true)
            ->first();
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('qty_on_hand');
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->stocks()->sum('qty_available');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereRaw('qty_on_hand <= products.min_stock');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereDoesntHave('stocks', function ($q) {
            $q->where('qty_on_hand', '>', 0);
        });
    }
}
