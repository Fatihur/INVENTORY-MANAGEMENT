<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'default_lead_time_days',
        'default_payment_terms',
        'is_active',
    ];

    protected $casts = [
        'default_lead_time_days' => 'integer',
        'default_payment_terms' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot(['is_primary', 'buy_price', 'moq', 'lead_time_days_override', 'supplier_sku'])
            ->withTimestamps();
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function getOnTimeDeliveryRate(): float
    {
        $completedOrders = $this->purchaseOrders()
            ->whereIn('status', ['received', 'closed'])
            ->count();

        if ($completedOrders === 0) {
            return 0.0;
        }

        $onTimeOrders = $this->purchaseOrders()
            ->whereIn('status', ['received', 'closed'])
            ->whereColumn('actual_delivery_date', '<=', 'expected_delivery_date')
            ->count();

        return round(($onTimeOrders / $completedOrders) * 100, 2);
    }

    public function getAverageLeadTime(): ?int
    {
        return $this->purchaseOrders()
            ->whereNotNull('actual_delivery_date')
            ->whereNotNull('order_date')
            ->selectRaw('AVG(DATEDIFF(actual_delivery_date, order_date)) as avg_lead_time')
            ->value('avg_lead_time');
    }
}
