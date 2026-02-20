<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SalesOrderApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API endpoints (no authentication required)
Route::prefix('v1')->group(function () {
    // Dashboard
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
    Route::get('/dashboard/sales-trend', [DashboardController::class, 'salesTrend']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/stock-levels', [DashboardController::class, 'stockLevels']);
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/inventory-value', [DashboardController::class, 'inventoryValue']);

    // Customers
    Route::apiResource('customers', CustomerApiController::class);

    // Products
    Route::apiResource('products', ProductApiController::class);

    // Sales Orders
    Route::apiResource('sales-orders', SalesOrderApiController::class);
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderApiController::class, 'confirm']);
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderApiController::class, 'cancel']);
});

// Protected API endpoints (authentication required)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Dashboard (protected)
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
    Route::get('/dashboard/sales-trend', [DashboardController::class, 'salesTrend']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/stock-levels', [DashboardController::class, 'stockLevels']);
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/inventory-value', [DashboardController::class, 'inventoryValue']);

    // Customers (protected)
    Route::apiResource('customers', CustomerApiController::class);

    // Products (protected)
    Route::apiResource('products', ProductApiController::class);

    // Sales Orders (protected)
    Route::apiResource('sales-orders', SalesOrderApiController::class);
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderApiController::class, 'confirm']);
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderApiController::class, 'cancel']);
});
