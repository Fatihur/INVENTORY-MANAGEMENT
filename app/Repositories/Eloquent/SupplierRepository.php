<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Models\Supplier;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Supplier
    {
        return $this->model->where('code', $code)->first();
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getWithPerformanceMetrics(): Collection
    {
        return $this->model
            ->select([
                'suppliers.*',
                DB::raw('(
                    SELECT COUNT(*)
                    FROM purchase_orders
                    WHERE purchase_orders.supplier_id = suppliers.id
                    AND purchase_orders.status IN ("received", "closed")
                ) as total_completed_orders'),
                DB::raw('(
                    SELECT COUNT(*)
                    FROM purchase_orders
                    WHERE purchase_orders.supplier_id = suppliers.id
                    AND purchase_orders.status IN ("received", "closed")
                    AND purchase_orders.actual_delivery_date <= purchase_orders.expected_delivery_date
                ) as on_time_orders'),
                DB::raw('(
                    SELECT AVG(DATEDIFF(actual_delivery_date, order_date))
                    FROM purchase_orders
                    WHERE purchase_orders.supplier_id = suppliers.id
                    AND purchase_orders.actual_delivery_date IS NOT NULL
                ) as avg_lead_time'),
            ])
            ->withCount('purchaseOrders')
            ->get();
    }
}
