<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockRepositoryInterface;
use App\Models\Stock;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class StockRepository extends BaseRepository implements StockRepositoryInterface
{
    public function __construct(Stock $model)
    {
        parent::__construct($model);
    }

    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Stock
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model
            ->where('product_id', $productId)
            ->with('warehouse')
            ->get();
    }

    public function getByWarehouse(int $warehouseId): Collection
    {
        return $this->model
            ->where('warehouse_id', $warehouseId)
            ->with('product')
            ->get();
    }

    public function getLowStock(): Collection
    {
        return $this->model
            ->select('stocks.*')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->whereColumn('stocks.qty_on_hand', '<=', 'products.min_stock')
            ->with(['product', 'warehouse'])
            ->get();
    }

    public function updateQuantity(int $stockId, int $quantity): bool
    {
        $stock = $this->find($stockId);

        if (!$stock) {
            return false;
        }

        return $stock->update([
            'qty_on_hand' => $quantity,
            'last_movement_at' => now(),
        ]);
    }
}
