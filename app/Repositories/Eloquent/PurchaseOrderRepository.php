<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseOrderRepository extends BaseRepository implements PurchaseOrderRepositoryInterface
{
    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);
    }

    public function findByPoNumber(string $poNumber): ?PurchaseOrder
    {
        return $this->model->where('po_number', $poNumber)->first();
    }

    public function getBySupplier(int $supplierId): Collection
    {
        return $this->model
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->whereIn('status', ['draft', 'sent', 'approved', 'partial'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['supplier', 'items.product', 'creator']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('po_number', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}
