<?php

namespace App\Livewire\Reports;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use Carbon\Carbon;
use Livewire\Component;

class ReportList extends Component
{
    public $activeReport = 'inventory';

    // Filters
    public $dateFrom;

    public $dateTo;

    public $warehouseFilter = 'all';

    public $productFilter = 'all';

    public $category_id = '';

    public $exportFormat = 'pdf';

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        // Load data for filters
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::orderBy('name')->get();

        return view('livewire.reports.report-list', [
            'warehouses' => $warehouses,
            'products' => $products,
        ])->title('Reports - Inventory Management');
    }

    public function setReport($report)
    {
        $this->activeReport = $report;
    }

    public function getReportData()
    {
        switch ($this->activeReport) {
            case 'inventory':
                return $this->getInventoryReportData();
            case 'sales':
                return $this->getSalesReportData();
            case 'purchase':
                return $this->getPurchaseReportData();
            case 'movement':
                return $this->getMovementReportData();
            case 'low_stock':
                return $this->getLowStockReportData();
            case 'expiry':
                return $this->getExpiryReportData();
            default:
                return [];
        }
    }

    protected function getInventoryReportData()
    {
        $query = Product::with(['stocks']);

        if ($this->productFilter && $this->productFilter !== 'all') {
            $query->where('id', $this->productFilter);
        }

        $products = $query->get()->map(function ($product) {
            $stockQty = $product->stocks->sum('quantity');

            return (object) [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category,
                'stock_qty' => $stockQty,
                'purchase_price' => $product->purchase_price ?? $product->cost_price ?? 0,
                'stock_value' => $stockQty * ($product->purchase_price ?? $product->cost_price ?? 0),
                'min_stock' => $product->min_stock,
            ];
        });

        $totalQuantity = $products->sum('stock_qty');
        $totalValue = $products->sum('stock_value');

        return [
            'total_products' => $products->count(),
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'products' => $products,
        ];
    }

    protected function getSalesReportData()
    {
        $query = SalesOrder::with(['customer', 'items'])
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo]);

        $orders = $query->get();

        // Sales by status
        $salesByStatus = [];
        foreach ($orders->groupBy('status') as $status => $group) {
            $salesByStatus[$status] = [
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
            ];
        }

        return [
            'total_sales' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'average_order' => $orders->avg('total_amount') ?? 0,
            'sales_by_status' => $salesByStatus,
            'orders' => $orders,
        ];
    }

    protected function getPurchaseReportData()
    {
        $query = PurchaseOrder::with(['supplier', 'items'])
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo]);

        $orders = $query->get();

        // Top suppliers
        $supplierData = [];
        foreach ($orders->groupBy('supplier_id') as $supplierId => $group) {
            $supplier = $group->first()->supplier;
            if ($supplier) {
                $supplierData[] = [
                    'name' => $supplier->name,
                    'orders' => $group->count(),
                    'total' => $group->sum('total_amount'),
                ];
            }
        }

        // Sort by total and take top 5
        usort($supplierData, fn ($a, $b) => $b['total'] <=> $a['total']);
        $topSuppliers = collect(array_slice($supplierData, 0, 5));

        return [
            'total_purchases' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'top_suppliers' => $topSuppliers,
            'orders' => $orders,
        ];
    }

    protected function getMovementReportData()
    {
        $query = StockMovement::with(['product', 'warehouse', 'user'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

        if ($this->warehouseFilter && $this->warehouseFilter !== 'all') {
            $query->where('warehouse_id', $this->warehouseFilter);
        }

        if ($this->productFilter && $this->productFilter !== 'all') {
            $query->where('product_id', $this->productFilter);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        // Movements by type
        $movementsByType = [];
        foreach ($movements->groupBy('type') as $type => $group) {
            $movementsByType[$type] = [
                'count' => $group->count(),
                'total_qty' => $group->sum('quantity'),
            ];
        }

        return [
            'total_movements' => $movements->count(),
            'movements_by_type' => $movementsByType,
            'movements' => $movements->map(function ($m) {
                $m->qty = $m->quantity;

                return $m;
            }),
        ];
    }

    protected function getLowStockReportData()
    {
        $products = Product::with(['stocks', 'supplier'])
            ->whereHas('stocks', function ($q) {
                $q->selectRaw('SUM(quantity) as total')
                    ->havingRaw('total <= products.min_stock');
            })
            ->orWhereDoesntHave('stocks')
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->stocks->sum('quantity');

                return $currentStock <= $product->min_stock;
            })
            ->map(function ($product) {
                $currentStock = $product->stocks->sum('quantity');
                $shortage = $product->min_stock - $currentStock;
                $product->current_stock = $currentStock;
                $product->shortage = max(0, $shortage);

                return $product;
            });

        $totalShortageValue = $products->sum(function ($p) {
            return $p->shortage * ($p->purchase_price ?? 0);
        });

        return [
            'total_low_stock' => $products->count(),
            'total_shortage_value' => $totalShortageValue,
            'products' => $products,
        ];
    }

    protected function getExpiryReportData()
    {
        $now = now();

        $expired = Batch::with('product')
            ->where('expiry_date', '<', $now)
            ->where('remaining_qty', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $expiring30 = Batch::with('product')
            ->where('expiry_date', '>=', $now)
            ->where('expiry_date', '<=', $now->copy()->addDays(30))
            ->where('remaining_qty', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $expiring60 = Batch::with('product')
            ->where('expiry_date', '>', $now->copy()->addDays(30))
            ->where('expiry_date', '<=', $now->copy()->addDays(60))
            ->where('remaining_qty', '>', 0)
            ->count();

        $expiring90 = Batch::with('product')
            ->where('expiry_date', '>', $now->copy()->addDays(60))
            ->where('expiry_date', '<=', $now->copy()->addDays(90))
            ->where('remaining_qty', '>', 0)
            ->count();

        return [
            'expired_count' => $expired->count(),
            'expiring_30_count' => $expiring30->count(),
            'expiring_60_count' => $expiring60,
            'expiring_90_count' => $expiring90,
            'expired' => $expired,
            'expiring_30' => $expiring30,
        ];
    }

    public function export()
    {
        // This would be implemented with Excel export functionality
        $this->dispatch('export-started');
    }
}
