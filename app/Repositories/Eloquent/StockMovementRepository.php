<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Models\StockMovement;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementRepository extends BaseRepository implements StockMovementRepositoryInterface
{
    public function __construct(StockMovement $model)
    {
        parent::__construct($model);
    }

    public function getByProduct(int $productId, int $limit = 50): Collection
    {
        return $this->model
            ->where('product_id', $productId)
            ->with(['warehouse', 'creator'])
            ->orderBy('moved_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByWarehouse(int $warehouseId, int $limit = 50): Collection
    {
        return $this->model
            ->where('warehouse_id', $warehouseId)
            ->with(['product', 'creator'])
            ->orderBy('moved_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByType(string $type, int $limit = 50): Collection
    {
        return $this->model
            ->where('type', $type)
            ->with(['product', 'warehouse', 'creator'])
            ->orderBy('moved_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getInDateRange(string $start, string $end): Collection
    {
        return $this->model
            ->whereBetween('moved_at', [$start, $end])
            ->with(['product', 'warehouse'])
            ->orderBy('moved_at', 'desc')
            ->get();
    }

    public function getRecent(int $limit = 20): Collection
    {
        return $this->model
            ->with(['product', 'warehouse', 'creator'])
            ->orderBy('moved_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['product', 'warehouse', 'creator']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('moved_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('moved_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('moved_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getDailyOutbound(int $productId, int $days = 30): Collection
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('moved_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(moved_at) as date, SUM(ABS(qty)) as total_out')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
