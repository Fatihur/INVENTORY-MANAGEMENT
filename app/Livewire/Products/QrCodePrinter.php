<?php

namespace App\Livewire\Products;

use App\Contracts\Services\QrCodeServiceInterface;
use App\Models\Product;
use Livewire\Component;

class QrCodePrinter extends Component
{
    public Product $product;
    public string $template = 'a4';

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function generateQr(QrCodeServiceInterface $qrService)
    {
        $qr = $qrService->generateForProduct($this->product);
        session()->flash('message', 'QR Code generated successfully.');
    }

    public function printLabels(QrCodeServiceInterface $qrService)
    {
        $selectedIds = $this->product->qrCodes->pluck('id')->toArray();
        if (empty($selectedIds)) {
            session()->flash('error', 'No QR codes to print.');
            return;
        }

        $html = $qrService->printLabels($selectedIds, $this->template);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="qr-labels.html"');
    }

    public function render()
    {
        return view('livewire.products.qr-code-printer', [
            'qrCodes' => $this->product->qrCodes,
        ]);
    }
}
