<?php

namespace App\Contracts\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;

interface PurchaseOrderServiceInterface
{
    public function createFromRecommendations(Collection $recommendations, int $userId): Collection;

    public function createDraft(int $supplierId, int $userId): PurchaseOrder;

    public function submitForApproval(int $poId): PurchaseOrder;

    public function approve(int $poId, int $approverId): PurchaseOrder;

    public function receiveGoods(int $poId, array $items, string $invoiceNumber, int $warehouseId, int $userId): GoodsReceipt;
}
