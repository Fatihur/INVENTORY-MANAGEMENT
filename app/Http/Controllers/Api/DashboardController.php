<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statistics()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => DB::table('suppliers')->count(),
            'total_warehouses' => Warehouse::count(),
            'low_stock_products' => Product::whereColumn('min_stock', '>', (
                DB::table('stocks')->selectRaw('COALESCE(SUM(qty_on_hand), 0)')
                    ->whereColumn('stocks.product_id', 'products.id')
            ))->count(),
            'expiring_batches' => Batch::where('is_active', true)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count(),
            'pending_orders' => SalesOrder::where('status', 'pending')->count(),
            'completed_orders_today' => SalesOrder::where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function salesTrend(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $salesTrend = SalesOrder::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $salesTrend,
        ]);
    }

    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);

        $topProducts = DB::table('sales_order_items')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.status', 'completed')
            ->selectRaw('products.id, products.name, products.sku, SUM(sales_order_items.quantity) as total_sold')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    public function stockLevels()
    {
        $stockLevels = DB::table('products')
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->leftJoin('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('products.id, products.name, products.sku, products.min_stock, 
                        COALESCE(SUM(stocks.qty_on_hand), 0) as total_qty,
                        GROUP_CONCAT(warehouses.name) as warehouses')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.min_stock')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stockLevels,
        ]);
    }

    public function recentOrders()
    {
        $recentOrders = SalesOrder::with(['customer', 'items'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentOrders,
        ]);
    }

    public function inventoryValue()
    {
        $inventoryValue = DB::table('products')
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->selectRaw('COALESCE(SUM(stocks.qty_on_hand * COALESCE(stocks.avg_cost, 0)), 0) as total_value')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $inventoryValue,
        ]);
    }
}
