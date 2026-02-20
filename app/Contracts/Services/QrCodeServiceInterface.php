<?php

namespace App\Contracts\Services;

use App\Models\Product;
use App\Models\ProductQr;

interface QrCodeServiceInterface
{
    public function generateForProduct(Product $product, string $type = 'product'): ProductQr;

    public function generateForBatch(Product $product, string $batchNumber, ?\DateTime $expiryDate): ProductQr;

    public function generateForLocation(int $warehouseId): ProductQr;

    public function getSvg(string $qrCodeValue): string;

    public function getPng(string $qrCodeValue, int $size = 200): string;

    public function decode(string $qrData): ?array;

    public function printLabels(array $qrIds, string $template = 'a4'): string;
}
