<?php

namespace App\Services\QrCode;

use App\Contracts\Services\QrCodeServiceInterface;
use App\Models\Product;
use App\Models\ProductQr;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

class QrCodeService implements QrCodeServiceInterface
{
    public function generateForProduct(Product $product, string $type = 'product'): ProductQr
    {
        return ProductQr::create([
            'product_id' => $product->id,
            'type' => $type,
        ]);
    }

    public function generateForBatch(Product $product, string $batchNumber, ?\DateTime $expiryDate): ProductQr
    {
        return ProductQr::create([
            'product_id' => $product->id,
            'type' => 'batch',
            'batch_number' => $batchNumber,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function generateForLocation(int $warehouseId): ProductQr
    {
        return ProductQr::create([
            'warehouse_id' => $warehouseId,
            'type' => 'location',
        ]);
    }

    public function getSvg(string $qrCodeValue): string
    {
        $qrCode = $this->createQrCode($qrCodeValue);
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    public function getPng(string $qrCodeValue, int $size = 200): string
    {
        $qrCode = $this->createQrCode($qrCodeValue)->setSize($size);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    public function decode(string $qrData): ?array
    {
        $parts = explode(':', $qrData);

        if (count($parts) < 2) {
            return null;
        }

        $type = $parts[0];
        $id = $parts[1] ?? null;

        return [
            'type' => $type,
            'id' => $id,
            'extra' => $parts[2] ?? null,
            'raw' => $qrData,
        ];
    }

    public function printLabels(array $qrIds, string $template = 'a4'): string
    {
        $qrs = ProductQr::whereIn('id', $qrIds)->with('product')->get();

        $qrs->each->markAsPrinted();

        return view('print.qr-labels', [
            'qrs' => $qrs,
            'template' => $template,
        ])->render();
    }

    private function createQrCode(string $data): QrCode
    {
        return new QrCode($data)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
    }
}
