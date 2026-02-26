<?php

namespace App\Services\PurchaseOrder;

use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Models\Batch;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService implements PurchaseOrderServiceInterface
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepository
    ) {}

    public function createFromRecommendations(Collection $recommendations, int $userId): Collection
    {
        $groupedBySupplier = $recommendations->groupBy('suggested_supplier.id');

        $createdPOs = collect();

        foreach ($groupedBySupplier as $supplierId => $items) {
            if (! $supplierId) {
                continue;
            }

            $po = $this->createDraft($supplierId, $userId);

            foreach ($items as $rec) {
                $product = $rec->product;
                $qty = $rec->metrics['recommended_order_qty'] ?? 0;
                $price = $product->getPrimarySupplier()?->pivot?->buy_price ?? 0;

                $po->items()->create([
                    'product_id' => $product->id,
                    'qty_ordered' => max($qty, 1),
                    'unit_price' => $price,
                    'total_price' => $price * max($qty, 1),
                ]);
            }

            $this->recalculateTotals($po);
            $createdPOs->push($po);
        }

        return $createdPOs;
    }

    public function createDraft(int $supplierId, int $userId): PurchaseOrder
    {
        return PurchaseOrder::create([
            'supplier_id' => $supplierId,
            'status' => 'draft',
            'order_date' => now(),
            'created_by' => $userId,
        ]);
    }

    public function submitForApproval(int $poId): PurchaseOrder
    {
        return DB::transaction(function () use ($poId) {
            // Lock the PO for update to prevent race conditions
            $po = PurchaseOrder::lockForUpdate()->find($poId);

            if (! $po || ! $po->canEdit()) {
                throw new \InvalidArgumentException('PO cannot be submitted');
            }

            $po->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return $po;
        });
    }

    public function approve(int $poId, int $approverId): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $approverId) {
            // Lock the PO for update to prevent race conditions
            $po = PurchaseOrder::lockForUpdate()->find($poId);

            if (! $po || ! $po->canApprove()) {
                throw new \InvalidArgumentException('PO cannot be approved');
            }

            $po->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            return $po;
        });
    }

    /**
     * Validate that the received quantity does not exceed ordered quantity.
     *
     * @throws \InvalidArgumentException
     */
    private function validateReceiptQty($poItem, int $qtyToReceive, array $item): void
    {
        $remainingQty = $poItem->qty_ordered - $poItem->qty_received;

        if ($qtyToReceive <= 0) {
            throw new \InvalidArgumentException(
                "Received quantity must be greater than 0 for product {$poItem->product->name}"
            );
        }

        if ($qtyToReceive > $remainingQty) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot receive %d units of %s. Ordered: %d, Already Received: %d, Remaining: %d',
                    $qtyToReceive,
                    $poItem->product->name,
                    $poItem->qty_ordered,
                    $poItem->qty_received,
                    $remainingQty
                )
            );
        }

        // Validate batch number is required for batch-tracked products
        $product = $poItem->product;
        if ($product->track_batch && empty($item['batch_number'])) {
            throw new \InvalidArgumentException(
                "Batch number is required for product {$product->name} (batch-tracked product)"
            );
        }

        // Validate expiry date if provided
        if (! empty($item['expiry_date'])) {
            $expiryDate = \Carbon\Carbon::parse($item['expiry_date']);
            $today = \Carbon\Carbon::today();

            if ($expiryDate->isPast()) {
                throw new \InvalidArgumentException(
                    "Cannot receive expired product {$poItem->product->name}. Expiry date: {$expiryDate->format('Y-m-d')}"
                );
            }

            if ($expiryDate->diffInDays($today) < 7) {
                throw new \InvalidArgumentException(
                    "Warning: Product {$poItem->product->name} expires in less than 7 days"
                );
            }
        }
    }

    public function receiveGoods(
        int $poId,
        array $items,
        string $invoiceNumber,
        int $warehouseId,
        int $userId
    ): GoodsReceipt {
        return DB::transaction(function () use ($poId, $items, $invoiceNumber, $warehouseId, $userId) {
            $po = $this->poRepository->find($poId);

            if (! $po || ! $po->canReceive()) {
                throw new \InvalidArgumentException('PO cannot receive goods');
            }

            $receipt = GoodsReceipt::create([
                'gr_number' => $this->generateGrNumber(),
                'purchase_order_id' => $poId,
                'supplier_id' => $po->supplier_id,
                'received_date' => now(),
                'invoice_number' => $invoiceNumber,
                'received_by' => $userId,
                'warehouse_id' => $warehouseId,
            ]);

            foreach ($items as $item) {
                $poItem = $po->items()->find($item['po_item_id']);

                if (! $poItem) {
                    continue;
                }

                // Validate received quantity
                $this->validateReceiptQty($poItem, $item['qty_received'], $item);

                $receiptItem = $receipt->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'qty_received' => $item['qty_received'],
                    'unit_cost' => $poItem->unit_price,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'warehouse_id' => $warehouseId,
                ]);

                // Create or update batch record if batch_number provided
                if (! empty($item['batch_number'])) {
                    $this->createOrUpdateBatch(
                        $poItem->product_id,
                        $warehouseId,
                        $item['batch_number'],
                        $item['qty_received'],
                        $item['expiry_date'] ?? null,
                        $poItem->unit_price,
                        $receipt->id
                    );
                }

                $poItem->increment('qty_received', $item['qty_received']);

                $this->updateStock($poItem->product_id, $warehouseId, $item['qty_received'], $poItem->unit_price);

                StockMovement::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => $warehouseId,
                    'type' => 'in',
                    'qty' => $item['qty_received'],
                    'qty_before' => $this->getStockBefore($poItem->product_id, $warehouseId),
                    'qty_after' => $this->getStockAfter($poItem->product_id, $warehouseId, $item['qty_received']),
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $receipt->id,
                    'created_by' => $userId,
                    'moved_at' => now(),
                ]);
            }

            // Update PO status based on receipt
            if ($po->isFullyReceived()) {
                $po->update(['status' => 'received', 'actual_delivery_date' => now()]);
            } else {
                $po->update(['status' => 'partial']);
            }

            return $receipt;
        });
    }

    /**
     * Create or update batch record for tracked products.
     */
    private function createOrUpdateBatch(
        int $productId,
        int $warehouseId,
        string $batchNumber,
        int $qty,
        ?string $expiryDate,
        float $costPrice,
        int $receiptId
    ): void {
        // Check if batch already exists
        $batch = Batch::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'batch_number' => $batchNumber,
        ])->first();

        if ($batch) {
            // Update existing batch
            $batch->increment('initial_qty', $qty);
            $batch->increment('remaining_qty', $qty);
        } else {
            // Create new batch
            Batch::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'initial_qty' => $qty,
                'remaining_qty' => $qty,
                'cost_price' => $costPrice,
                'receipt_id' => $receiptId,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Close a partially received PO (when remaining items won't be received).
     */
    public function closePartial(int $poId, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $userId) {
            // Lock the PO for update to prevent race conditions
            $po = PurchaseOrder::lockForUpdate()->find($poId);

            if (! $po || $po->status !== 'partial') {
                throw new \InvalidArgumentException('Only partial POs can be closed');
            }

            $po->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $userId,
                'notes' => $po->notes."\n[Closed by user {$userId} at ".now()->format('Y-m-d H:i:s').']',
            ]);

            return $po;
        });
    }

    private function updateStock(int $productId, int $warehouseId, int $qty, float $unitCost): void
    {
        $stock = Stock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['qty_on_hand' => 0, 'avg_cost' => 0]
        );

        $totalValue = ($stock->qty_on_hand * $stock->avg_cost) + ($qty * $unitCost);
        $newQty = $stock->qty_on_hand + $qty;
        $newAvgCost = $newQty > 0 ? $totalValue / $newQty : 0;

        $stock->update([
            'qty_on_hand' => $newQty,
            'avg_cost' => $newAvgCost,
            'last_movement_at' => now(),
        ]);
    }

    private function recalculateTotals(PurchaseOrder $po): void
    {
        $subtotal = $po->items->sum('total_price');
        // Use configurable tax rate instead of hardcoded 11%
        $taxRate = config('inventory.tax_rate', 0.11);
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;

        $po->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
        ]);
    }

    private function generateGrNumber(): string
    {
        // Use database transaction with lock to prevent duplicate numbers
        return DB::transaction(function () {
            $count = GoodsReceipt::whereYear('created_at', now()->year)->lockForUpdate()->count() + 1;

            return 'GR-'.now()->format('Y').'-'.str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    private function getStockBefore(int $productId, int $warehouseId): int
    {
        $stock = Stock::where(['product_id' => $productId, 'warehouse_id' => $warehouseId])->first();

        return $stock?->qty_on_hand ?? 0;
    }

    private function getStockAfter(int $productId, int $warehouseId, int $qtyReceived): int
    {
        return $this->getStockBefore($productId, $warehouseId) + $qtyReceived;
    }
}
