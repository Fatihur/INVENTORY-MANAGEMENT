<?php

namespace App\Contracts\Repositories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;

interface StockRepositoryInterface extends BaseRepositoryInterface
{
    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Stock;

    public function getByProduct(int $productId): Collection;

    public function getByWarehouse(int $warehouseId): Collection;

    public function getLowStock(): Collection;

    public function updateQuantity(int $stockId, int $quantity): bool;
}
