<?php

namespace App\Contracts\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

interface RestockRecommendationServiceInterface
{
    public function getRecommendations(): Collection;

    public function getBySupplier(int $supplierId): Collection;

    public function calculateMetrics(Product $product): array;

    public function calculatePriority(array $metrics): string;

    public function getRecommendedOrderQuantity(Product $product): int;
}
