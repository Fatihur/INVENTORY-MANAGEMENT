<?php

namespace App\Services\Restock;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Services\RestockRecommendationServiceInterface;
use App\Models\Product;
use Illuminate\Support\Collection;

class RestockRecommendationService implements RestockRecommendationServiceInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function getRecommendations(): Collection
    {
        $products = Product::active()
            ->with(['stocks', 'suppliers', 'primarySupplier'])
            ->get();

        return $products->map(function ($product) {
            $metrics = $this->calculateMetrics($product);

            return (object) [
                'product' => $product,
                'metrics' => $metrics,
                'priority' => $this->calculatePriority($metrics),
                'suggested_supplier' => $product->primarySupplier,
            ];
        })->filter(fn ($rec) => $rec->metrics['needs_restock'])
          ->sortByDesc('priority');
    }

    public function getBySupplier(int $supplierId): Collection
    {
        return $this->getRecommendations()
            ->filter(fn ($rec) => $rec->suggested_supplier?->id === $supplierId);
    }

    public function calculateMetrics(Product $product): array
    {
        return $this->productRepository->getStockMetrics($product->id);
    }

    public function calculatePriority(array $metrics): string
    {
        $currentStock = $metrics['current_stock'];
        $rop = $metrics['reorder_point'];
        $daysUntilStockout = $metrics['days_until_stockout'] ?? 999;

        if ($currentStock <= 0) {
            return 'critical';
        }

        if ($currentStock <= $rop * 0.5) {
            return 'high';
        }

        if ($daysUntilStockout <= 7) {
            return 'medium';
        }

        return 'low';
    }

    public function getRecommendedOrderQuantity(Product $product): int
    {
        $metrics = $this->calculateMetrics($product);
        $moq = $product->primarySupplier?->pivot?->moq ?? 1;

        $recommendedQty = $metrics['recommended_order_qty'] ?? 0;

        return max($moq, ceil($recommendedQty / $moq) * $moq);
    }
}
