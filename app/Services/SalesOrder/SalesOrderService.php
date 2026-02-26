<?php

namespace App\Services\SalesOrder;

use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    /**
     * Get available quantity for a product in a warehouse.
     */
    public function getAvailableQty(int $productId, int $warehouseId): int
    {
        $stock = Stock::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])->first();

        return $stock ? $stock->qty_on_hand - $stock->qty_reserved : 0;
    }

    /**
     * Get actual quantity on hand for a product in a warehouse.
     */
    public function getQtyOnHand(int $productId, int $warehouseId): int
    {
        $stock = Stock::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])->first();

        return $stock ? $stock->qty_on_hand : 0;
    }

    /**
     * Reserve stock when order is confirmed.
     * Validates and reserves atomically to prevent race conditions.
     */
    public function reserveStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            $warehouseId = $salesOrder->warehouse_id;

            // Get all product IDs to lock them in a single query (prevents deadlocks)
            $productIds = $salesOrder->items->pluck('product_id')->toArray();

            // Lock all required stock records in consistent order (prevents deadlocks)
            sort($productIds);
            $stocks = Stock::lockForUpdate()
                ->where('warehouse_id', $warehouseId)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($salesOrder->items as $item) {
                $stock = $stocks->get($item->product_id);

                if (! $stock) {
                    throw new \InvalidArgumentException(
                        "No stock found for product {$item->product->name} in warehouse"
                    );
                }

                $availableQty = $stock->qty_on_hand - $stock->qty_reserved;

                if ($availableQty < $item->quantity) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Insufficient stock for %s. Available: %d, Reserved: %d, Required: %d',
                            $item->product->name,
                            $availableQty,
                            $stock->qty_reserved,
                            $item->quantity
                        )
                    );
                }

                // Update reserved quantity
                $stock->increment('qty_reserved', $item->quantity);
            }
        });
    }

    /**
     * Release reserved stock (when order is cancelled).
     */
    public function releaseReservedStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            $warehouseId = $salesOrder->warehouse_id;

            foreach ($salesOrder->items as $item) {
                $stock = Stock::lockForUpdate()
                    ->where([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId,
                    ])
                    ->first();

                if ($stock && $stock->qty_reserved >= $item->quantity) {
                    $stock->decrement('qty_reserved', $item->quantity);
                }
            }
        });
    }

    /**
     * Deduct stock when order is shipped.
     */
    public function deductStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            $warehouseId = $salesOrder->warehouse_id;

            foreach ($salesOrder->items as $item) {
                $stock = Stock::lockForUpdate()
                    ->where([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId,
                    ])
                    ->first();

                if (! $stock) {
                    throw new \InvalidArgumentException(
                        "No stock found for product {$item->product->name}"
                    );
                }

                $qtyBefore = $stock->qty_on_hand;
                $qtyAfter = $qtyBefore - $item->quantity;

                if ($qtyAfter < 0) {
                    throw new \InvalidArgumentException(
                        "Insufficient stock for {$item->product->name}"
                    );
                }

                // Update stock
                $stock->update([
                    'qty_on_hand' => $qtyAfter,
                    'qty_reserved' => max(0, $stock->qty_reserved - $item->quantity),
                    'last_movement_at' => now(),
                ]);

                // Create movement record
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $warehouseId,
                    'type' => 'out',
                    'qty' => -$item->quantity,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'reference_type' => SalesOrder::class,
                    'reference_id' => $salesOrder->id,
                    'notes' => "Sales Order {$salesOrder->so_number}",
                    'created_by' => Auth::id(),
                    'moved_at' => now(),
                ]);
            }
        });
    }

    /**
     * Process order confirmation with stock validation and reservation.
     * Validation and reservation are done atomically in a single transaction.
     */
    public function confirmOrder(SalesOrder $salesOrder): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder) {
            // Lock the sales order to prevent concurrent status updates
            $salesOrder = SalesOrder::lockForUpdate()->find($salesOrder->id);

            if ($salesOrder->status !== 'draft') {
                throw new \InvalidArgumentException('Only draft orders can be confirmed');
            }

            // Validate warehouse is assigned
            if (empty($salesOrder->warehouse_id)) {
                throw new \InvalidArgumentException('Warehouse must be assigned before confirming order');
            }

            // Reserve stock (includes validation within transaction)
            $this->reserveStock($salesOrder);

            // Update status
            $salesOrder->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            return $salesOrder;
        });
    }

    /**
     * Process order shipping with stock deduction.
     */
    public function shipOrder(SalesOrder $salesOrder, ?string $trackingNumber = null): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $trackingNumber) {
            // Lock the sales order to prevent concurrent status updates
            $salesOrder = SalesOrder::lockForUpdate()->find($salesOrder->id);

            if (! in_array($salesOrder->status, ['confirmed', 'processing'])) {
                throw new \InvalidArgumentException('Only confirmed or processing orders can be shipped');
            }

            // Deduct stock (also handles locking)
            $this->deductStock($salesOrder);

            // Update status
            $salesOrder->update([
                'status' => 'shipped',
                'shipped_at' => now(),
                'tracking_number' => $trackingNumber,
            ]);

            return $salesOrder;
        });
    }

    /**
     * Cancel order and release reserved stock.
     */
    public function cancelOrder(SalesOrder $salesOrder): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder) {
            // Lock the sales order to prevent concurrent status updates
            $salesOrder = SalesOrder::lockForUpdate()->find($salesOrder->id);

            if (! in_array($salesOrder->status, ['draft', 'confirmed', 'processing'])) {
                throw new \InvalidArgumentException('Cannot cancel order with status: '.$salesOrder->status);
            }

            // Release reserved stock if order was confirmed or processing
            if ($salesOrder->status === 'confirmed' || $salesOrder->status === 'processing') {
                $this->releaseReservedStock($salesOrder);
            }

            $salesOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $salesOrder;
        });
    }
}
