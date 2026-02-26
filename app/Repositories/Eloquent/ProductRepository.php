<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function findByQrCode(string $qrCode): ?Product
    {
        return $this->model->whereHas('qrCodes', function ($q) use ($qrCode) {
            $q->where('qr_code_value', $qrCode);
        })->first();
    }

    public function getLowStock(): Collection
    {
        return $this->model
            ->select('products.*')
            ->join('stocks', 'products.id', '=', 'stocks.product_id')
            ->whereColumn('stocks.qty_on_hand', '<=', 'products.min_stock')
            ->where('products.is_active', true)
            ->with(['stocks', 'suppliers'])
            ->get();
    }

    public function getOutOfStock(): Collection
    {
        return $this->model
            ->whereDoesntHave('stocks', function ($q) {
                $q->where('qty_on_hand', '>', 0);
            })
            ->where('products.is_active', true)
            ->with(['stocks', 'suppliers'])
            ->get();
    }

    public function getWithStocks(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['stocks.warehouse', 'qrCodes']);

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'low' => $query->lowStock(),
                'out' => $query->outOfStock(),
                default => null,
            };
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getStockMetrics(int $productId): array
    {
        $product = $this->find($productId);

        if (! $product) {
            return [];
        }

        $lookbackDays = config('inventory.stock.adu_lookback_days', 30);

        $movements = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('moved_at', '>=', now()->subDays($lookbackDays))
            ->selectRaw('DATE(moved_at) as date, SUM(ABS(qty)) as total_out')
            ->groupBy('date')
            ->pluck('total_out', 'date');

        // Calculate ADU based on actual days with data, not always 30
        $daysWithData = $movements->count();
        $adu = $daysWithData > 0
            ? round($movements->sum() / $daysWithData, 2)
            : 0;

        $currentStock = $product->total_stock;
        $leadTime = $product->lead_time_days;
        $safetyStock = $product->safety_stock;

        $rop = ($adu * $leadTime) + $safetyStock;
        $daysUntilStockout = $adu > 0 ? round($currentStock / $adu) : null;
        $stockoutDate = $daysUntilStockout ? now()->addDays((int) $daysUntilStockout)->format('Y-m-d') : null;

        return [
            'product_id' => $productId,
            'current_stock' => $currentStock,
            'adu' => $adu,
            'lead_time_days' => $leadTime,
            'safety_stock' => $safetyStock,
            'reorder_point' => $rop,
            'needs_restock' => $currentStock <= $rop,
            'days_until_stockout' => $daysUntilStockout,
            'estimated_stockout_date' => $stockoutDate,
            'recommended_order_qty' => max(0, ($adu * ($leadTime + 14)) + $safetyStock - $currentStock),
        ];
    }
}
