<?php

namespace App\Services\Stock;

use App\Contracts\Repositories\StockRepositoryInterface;
use App\Contracts\Services\StockServiceInterface;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService implements StockServiceInterface
{
    public function __construct(
        private StockRepositoryInterface $stockRepository
    ) {}

    public function adjustStock(
        int $productId,
        int $warehouseId,
        int $newQty,
        string $reason,
        int $userId
    ): StockMovement {
        return DB::transaction(function () use ($productId, $warehouseId, $newQty, $reason, $userId) {
            // Lock the stock record to prevent race conditions
            $stock = Stock::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['qty_on_hand' => 0, 'qty_reserved' => 0]
            );

            $qtyBefore = $stock->qty_on_hand;
            $difference = $newQty - $qtyBefore;

            // Prevent negative stock after adjustment
            if ($newQty < 0) {
                throw new \InvalidArgumentException('Stock quantity cannot be negative');
            }

            // Prevent adjusting below reserved quantity
            if ($newQty < $stock->qty_reserved) {
                throw new \InvalidArgumentException(
                    sprintf('Cannot adjust below reserved quantity. Reserved: %d, Requested: %d', $stock->qty_reserved, $newQty)
                );
            }

            $stock->update([
                'qty_on_hand' => $newQty,
                'last_movement_at' => now(),
            ]);

            return StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'adjust',
                'qty' => $difference,
                'qty_before' => $qtyBefore,
                'qty_after' => $newQty,
                'notes' => $reason,
                'created_by' => $userId,
                'moved_at' => now(),
            ]);
        });
    }

    public function stockOut(
        int $productId,
        int $warehouseId,
        int $qty,
        string $reason,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMovement {
        return DB::transaction(function () use ($productId, $warehouseId, $qty, $reason, $userId, $referenceType, $referenceId) {
            // Lock the stock record to prevent race conditions
            $stock = Stock::lockForUpdate()
                ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
                ->first();

            if (!$stock) {
                throw new \InvalidArgumentException('Stock not found for product in this warehouse');
            }

            if ($stock->qty_on_hand < $qty) {
                throw new \InvalidArgumentException(
                    sprintf('Insufficient stock. Available: %d, Requested: %d', $stock->qty_on_hand, $qty)
                );
            }

            $qtyBefore = $stock->qty_on_hand;
            $qtyAfter = $qtyBefore - $qty;

            $stock->update([
                'qty_on_hand' => $qtyAfter,
                'last_movement_at' => now(),
            ]);

            return StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'out',
                'qty' => -$qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $reason,
                'created_by' => $userId,
                'moved_at' => now(),
            ]);
        });
    }

    public function stockIn(
        int $productId,
        int $warehouseId,
        int $qty,
        float $unitCost,
        string $reason,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMovement {
        return DB::transaction(function () use ($productId, $warehouseId, $qty, $unitCost, $reason, $userId, $referenceType, $referenceId) {
            // Lock the stock record to prevent race conditions
            $stock = Stock::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['qty_on_hand' => 0, 'avg_cost' => 0]
            );

            $qtyBefore = $stock->qty_on_hand;
            $qtyAfter = $qtyBefore + $qty;

            // Calculate new average cost using weighted average
            $totalValue = ($qtyBefore * $stock->avg_cost) + ($qty * $unitCost);
            $newAvgCost = $qtyAfter > 0 ? $totalValue / $qtyAfter : 0;

            $stock->update([
                'qty_on_hand' => $qtyAfter,
                'avg_cost' => $newAvgCost,
                'last_movement_at' => now(),
            ]);

            return StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'qty' => $qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $reason,
                'created_by' => $userId,
                'moved_at' => now(),
            ]);
        });
    }

    public function transfer(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $qty,
        int $userId
    ): array {
        // Validate warehouses are different
        if ($fromWarehouseId === $toWarehouseId) {
            throw new \InvalidArgumentException('Source and destination warehouses must be different');
        }

        return DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $qty, $userId) {
            // Lock both stocks in consistent order to prevent deadlocks
            // Order by warehouse_id to ensure consistent locking order
            $stockIds = [$fromWarehouseId, $toWarehouseId];
            sort($stockIds);

            $stocks = collect($stockIds)->mapWithKeys(function ($warehouseId) use ($productId) {
                $stock = Stock::lockForUpdate()
                    ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
                    ->first();

                if (!$stock) {
                    // Create placeholder for missing stock
                    $stock = Stock::make([
                        'product_id' => $productId,
                        'warehouse_id' => $warehouseId,
                        'qty_on_hand' => 0,
                        'avg_cost' => 0,
                    ]);
                }

                return [$warehouseId => $stock];
            });

            $fromStock = $stocks[$fromWarehouseId];
            $toStock = $stocks[$toWarehouseId];

            if (!$fromStock->exists) {
                throw new \InvalidArgumentException('Source stock not found');
            }

            // Check for sufficient stock (including reserved)
            $availableQty = $fromStock->qty_on_hand - $fromStock->qty_reserved;
            if ($availableQty < $qty) {
                throw new \InvalidArgumentException(
                    sprintf('Insufficient available stock. Available: %d, Reserved: %d, Requested: %d', $availableQty, $fromStock->qty_reserved, $qty)
                );
            }

            // Use the source warehouse's avg_cost for the transfer
            $transferCost = $fromStock->avg_cost;

            // Perform stock out (inline to avoid nested transaction)
            $fromQtyBefore = $fromStock->qty_on_hand;
            $fromQtyAfter = $fromQtyBefore - $qty;

            if ($fromStock->exists) {
                $fromStock->update([
                    'qty_on_hand' => $fromQtyAfter,
                    'last_movement_at' => now(),
                ]);
            }

            // Create out movement (reference_id will be updated after in movement)
            $outMovement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'type' => 'transfer_out',
                'qty' => -$qty,
                'qty_before' => $fromQtyBefore,
                'qty_after' => $fromQtyAfter,
                'reference_type' => StockMovement::class,
                'reference_id' => null, // Will be updated after creating in movement
                'notes' => "Transfer to warehouse {$toWarehouseId}",
                'created_by' => $userId,
                'moved_at' => now(),
            ]);

            // Perform stock in to destination
            $toQtyBefore = $toStock->qty_on_hand;
            $toQtyAfter = $toQtyBefore + $qty;

            // Calculate new average cost for destination warehouse
            $totalValue = ($toQtyBefore * $toStock->avg_cost) + ($qty * $transferCost);
            $newAvgCost = $toQtyAfter > 0 ? $totalValue / $toQtyAfter : 0;

            if ($toStock->exists) {
                $toStock->update([
                    'qty_on_hand' => $toQtyAfter,
                    'avg_cost' => $newAvgCost,
                    'last_movement_at' => now(),
                ]);
            } else {
                Stock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $toWarehouseId,
                    'qty_on_hand' => $toQtyAfter,
                    'avg_cost' => $newAvgCost,
                    'last_movement_at' => now(),
                ]);
            }

            // Create the in movement
            $inMovement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'type' => 'transfer_in',
                'qty' => $qty,
                'qty_before' => $toQtyBefore,
                'qty_after' => $toQtyAfter,
                'reference_type' => StockMovement::class,
                'reference_id' => $outMovement->id,
                'notes' => "Transfer from warehouse {$fromWarehouseId}",
                'created_by' => $userId,
                'moved_at' => now(),
            ]);

            // Update out movement to link back to in movement
            $outMovement->update(['reference_id' => $inMovement->id]);

            return ['out' => $outMovement, 'in' => $inMovement];
        });
    }

    /**
     * Check if sufficient stock is available without modifying it.
     * Use this for validation before stock operations.
     */
    public function checkAvailability(int $productId, int $warehouseId, int $requiredQty): bool
    {
        $stock = Stock::where(['product_id' => $productId, 'warehouse_id' => $warehouseId])->first();
        return $stock && $stock->qty_on_hand >= $requiredQty;
    }

    /**
     * Get available stock quantity.
     */
    public function getAvailableQty(int $productId, int $warehouseId): int
    {
        $stock = Stock::where(['product_id' => $productId, 'warehouse_id' => $warehouseId])->first();
        return $stock ? $stock->qty_on_hand : 0;
    }
}
