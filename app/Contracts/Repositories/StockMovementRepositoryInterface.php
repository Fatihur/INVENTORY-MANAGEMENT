<?php

namespace App\Contracts\Repositories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface extends BaseRepositoryInterface
{
    public function getByProduct(int $productId, int $limit = 50): Collection;

    public function getByWarehouse(int $warehouseId, int $limit = 50): Collection;

    public function getByType(string $type, int $limit = 50): Collection;

    public function getInDateRange(string $start, string $end): Collection;

    public function getRecent(int $limit = 20): Collection;

    public function getWithFilters(array $filters): LengthAwarePaginator;

    public function getDailyOutbound(int $productId, int $days = 30): Collection;
}
