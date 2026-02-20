<?php

namespace App\Services\Forecasting;

use App\Contracts\Services\ForecastingServiceInterface;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ForecastingService implements ForecastingServiceInterface
{
    public function calculateMovingAverage(int $productId, int $days = 7): float
    {
        $movements = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('moved_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(moved_at) as date, SUM(ABS(qty)) as total_out')
            ->groupBy('date')
            ->pluck('total_out');

        if ($movements->isEmpty()) {
            return 0.0;
        }

        return round($movements->sum() / $days, 2);
    }

    public function predictStockoutDate(int $productId): ?\DateTime
    {
        $product = Product::find($productId);

        if (!$product) {
            return null;
        }

        $currentStock = $product->total_stock;
        $adu = $this->calculateMovingAverage($productId, 30);

        if ($adu <= 0) {
            return null;
        }

        $daysUntilStockout = (int) ceil($currentStock / $adu);

        return now()->addDays($daysUntilStockout)->toDateTime();
    }

    public function getDemandTrend(int $productId, int $weeks = 12): Collection
    {
        return DB::table('stock_movements')
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('moved_at', '>=', now()->subWeeks($weeks))
            ->selectRaw('YEARWEEK(moved_at) as week, SUM(ABS(qty)) as total_demand')
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }

    public function getFastMovingItems(int $limit = 10): Collection
    {
        return DB::table('stock_movements')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->where('stock_movements.type', 'out')
            ->where('stock_movements.moved_at', '>=', now()->subDays(30))
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                SUM(ABS(stock_movements.qty)) as total_out
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_out')
            ->limit($limit)
            ->get();
    }

    public function getSlowMovingItems(int $limit = 10): Collection
    {
        return DB::table('products')
            ->leftJoin('stock_movements', function ($join) {
                $join->on('products.id', '=', 'stock_movements.product_id')
                    ->where('stock_movements.type', 'out')
                    ->where('stock_movements.moved_at', '>=', now()->subDays(90));
            })
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                COALESCE(SUM(ABS(stock_movements.qty)), 0) as total_out
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->havingRaw('total_out < 5')
            ->orderBy('total_out')
            ->limit($limit)
            ->get();
    }
}
