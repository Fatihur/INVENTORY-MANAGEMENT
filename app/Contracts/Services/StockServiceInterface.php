<?php

namespace App\Contracts\Services;

use App\Models\StockMovement;

interface StockServiceInterface
{
    public function adjustStock(int $productId, int $warehouseId, int $newQty, string $reason, int $userId): StockMovement;

    public function stockOut(int $productId, int $warehouseId, int $qty, string $reason, int $userId, ?string $referenceType = null, ?int $referenceId = null): StockMovement;

    public function stockIn(int $productId, int $warehouseId, int $qty, float $unitCost, string $reason, int $userId, ?string $referenceType = null, ?int $referenceId = null): StockMovement;

    public function transfer(int $productId, int $fromWarehouseId, int $toWarehouseId, int $qty, int $userId): array;
}
