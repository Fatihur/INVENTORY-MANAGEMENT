<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $table = 'supplier_products';

    protected $fillable = [
        'supplier_id',
        'product_id',
        'is_primary',
        'buy_price',
        'moq',
        'lead_time_days_override',
        'supplier_sku',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'buy_price' => 'decimal:2',
        'moq' => 'integer',
        'lead_time_days_override' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
