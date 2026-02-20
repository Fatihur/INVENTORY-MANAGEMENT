<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySku(string $sku): ?Product;

    public function findByQrCode(string $qrCode): ?Product;

    public function getLowStock(): Collection;

    public function getOutOfStock(): Collection;

    public function getWithStocks(array $filters = []): LengthAwarePaginator;

    public function getStockMetrics(int $productId): array;
}
