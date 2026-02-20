<?php

namespace App\Contracts\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPoNumber(string $poNumber): ?PurchaseOrder;

    public function getBySupplier(int $supplierId): Collection;

    public function getByStatus(string $status): Collection;

    public function getPending(): Collection;

    public function getWithFilters(array $filters): LengthAwarePaginator;
}
