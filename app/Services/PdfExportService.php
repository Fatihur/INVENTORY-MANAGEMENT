<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfExportService
{
    public function generate(string $view, array $data, string $filename, bool $download = true)
    {
        $pdf = Pdf::loadView($view, $data);

        if ($download) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function save(string $view, array $data, string $path)
    {
        $pdf = Pdf::loadView($view, $data);
        $content = $pdf->output();

        Storage::put($path, $content);

        return Storage::path($path);
    }

    public function salesOrder($salesOrder)
    {
        $data = [
            'salesOrder' => $salesOrder->load(['customer', 'customer.addresses', 'items.product', 'warehouse']),
            'companyName' => config('app.name'),
            'generatedAt' => now(),
        ];

        return $this->generate('pdf.sales-order', $data, "SO-{$salesOrder->so_number}.pdf");
    }

    public function purchaseOrder($purchaseOrder)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product', 'warehouse']),
            'companyName' => config('app.name'),
            'generatedAt' => now(),
        ];

        return $this->generate('pdf.purchase-order', $data, "PO-{$purchaseOrder->po_number}.pdf");
    }

    public function deliveryOrder($salesOrder)
    {
        $data = [
            'salesOrder' => $salesOrder->load(['customer', 'items.product']),
            'companyName' => config('app.name'),
            'generatedAt' => now(),
        ];

        return $this->generate('pdf.delivery-order', $data, "DO-{$salesOrder->so_number}.pdf");
    }

    public function inventoryReport(array $filters)
    {
        $data = [
            'products' => $filters['products'] ?? [],
            'warehouses' => $filters['warehouses'] ?? [],
            'generatedAt' => now(),
            'filters' => $filters,
        ];

        return $this->generate('pdf.inventory-report', $data, 'inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
