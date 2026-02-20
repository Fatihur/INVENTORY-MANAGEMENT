<?php

use App\Livewire\Approvals\ApprovalList;
use App\Livewire\Batches\BatchList;
use App\Livewire\BinLocations\BinLocationList;
use App\Livewire\Customers;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Products\ProductDetail;
use App\Livewire\Products\ProductForm;
use App\Livewire\Products\ProductList;
use App\Livewire\Products\QrCodePrinter;
use App\Livewire\PurchaseOrders\GoodsReceipt;
use App\Livewire\PurchaseOrders\PurchaseOrderDetail;
use App\Livewire\PurchaseOrders\PurchaseOrderForm;
use App\Livewire\PurchaseOrders\PurchaseOrderList;
use App\Livewire\Reports\ReportList;
use App\Livewire\Restock\RestockRecommendations;
use App\Livewire\SalesOrders;
use App\Livewire\Settings\ScannerSettings;
use App\Livewire\Stock\StockAdjustment;
use App\Livewire\Stock\StockIn;
use App\Livewire\Stock\StockList;
use App\Livewire\Stock\StockOut;
use App\Livewire\Stock\StockScanner;
use App\Livewire\Stock\StockTransfer;
use App\Livewire\StockOpname\StockOpnameList;
use App\Livewire\Suppliers\SupplierForm;
use App\Livewire\Suppliers\SupplierList;
use App\Livewire\Suppliers\SupplierPerformance;
use App\Livewire\Suppliers\SupplierProducts;
use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserList;
use App\Livewire\Warehouses\WarehouseList;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Dashboard route (simple view)
Route::get('/dashboard-view', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard-view');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware(['role:owner|admin'])->group(function () {
        Route::get('/products/create', ProductForm::class)->name('products.create');
    });

    Route::middleware(['role:owner|admin|warehouse|purchasing|manager'])->group(function () {
        Route::get('/products', ProductList::class)->name('products.index');
        Route::get('/products/{product}', ProductDetail::class)->name('products.show');
    });

    Route::middleware(['role:owner|admin'])->group(function () {
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
        Route::get('/products/{product}/qr-print', QrCodePrinter::class)->name('products.qr-print');
    });

    Route::middleware(['role:owner|admin|warehouse'])->prefix('stock')->group(function () {
        Route::get('/', StockList::class)->name('stock.index');
        Route::get('/scanner', StockScanner::class)->name('stock.scanner');
        Route::get('/in', StockIn::class)->name('stock.in');
        Route::get('/out', StockOut::class)->name('stock.out');
        Route::get('/transfer', StockTransfer::class)->name('stock.transfer');
        Route::get('/adjust', StockAdjustment::class)->name('stock.adjust');
    });

    Route::middleware(['role:owner|admin|purchasing'])->group(function () {
        Route::get('/suppliers/create', SupplierForm::class)->name('suppliers.create');
    });

    Route::middleware(['role:owner|admin|purchasing|manager'])->group(function () {
        Route::get('/suppliers', SupplierList::class)->name('suppliers.index');
        Route::get('/suppliers/{supplier}', SupplierPerformance::class)->name('suppliers.show');
        Route::get('/suppliers/{supplier}/products', SupplierProducts::class)->name('suppliers.products');
    });

    Route::middleware(['role:owner|admin|purchasing'])->group(function () {
        Route::get('/suppliers/{supplier}/edit', SupplierForm::class)->name('suppliers.edit');
    });

    Route::middleware(['role:owner|admin'])->group(function () {
        Route::get('/users/create', UserForm::class)->name('users.create');
        Route::get('/users', UserList::class)->name('users.index');
        Route::get('/users/{user}/edit', UserForm::class)->name('users.edit');
    });

    Route::middleware(['role:owner|admin|purchasing'])->prefix('purchase-orders')->group(function () {
        Route::get('/', PurchaseOrderList::class)->name('purchase-orders.index');
        Route::get('/create', PurchaseOrderForm::class)->name('purchase-orders.create');
        Route::get('/{purchaseOrder}/edit', PurchaseOrderForm::class)->name('purchase-orders.edit');
        Route::get('/{purchaseOrder}/receive', GoodsReceipt::class)->name('purchase-orders.receive');
        Route::get('/{purchaseOrder}', PurchaseOrderDetail::class)->name('purchase-orders.show');
    });

    Route::middleware(['role:owner|admin|purchasing'])->group(function () {
        Route::get('/restock-recommendations', RestockRecommendations::class)
            ->name('restock.recommendations');
    });

    // Customer Management
    Route::middleware(['role:owner|admin|manager'])->group(function () {
        Route::get('/customers', Customers::class)->name('customers.index');
    });

    // Sales Order Management
    Route::middleware(['role:owner|admin|manager'])->prefix('sales-orders')->group(function () {
        Route::get('/', SalesOrders::class)->name('sales-orders.index');
    });

    // Warehouse Management
    Route::middleware(['role:owner|admin|warehouse'])->group(function () {
        Route::get('/warehouses', WarehouseList::class)->name('warehouses.index');
    });

    // Batch Management
    Route::middleware(['role:owner|admin|warehouse'])->group(function () {
        Route::get('/batches', BatchList::class)->name('batches.index');
    });

    // Stock Opname
    Route::middleware(['role:owner|admin|warehouse'])->group(function () {
        Route::get('/stock-opname', StockOpnameList::class)->name('stock-opname.index');
    });

    // Bin Locations
    Route::middleware(['role:owner|admin|warehouse'])->group(function () {
        Route::get('/bin-locations', BinLocationList::class)->name('bin-locations.index');
    });

    // Approvals
    Route::middleware(['role:owner|admin|manager'])->group(function () {
        Route::get('/approvals', ApprovalList::class)->name('approvals.index');
    });

    // Reports
    Route::middleware(['role:owner|admin|warehouse|purchasing|manager'])->group(function () {
        Route::get('/reports', ReportList::class)->name('reports.index');
    });

    // Device Settings
    Route::middleware(['role:owner|admin|warehouse'])->prefix('settings')->group(function () {
        Route::get('/scanner', ScannerSettings::class)->name('settings.scanner');
    });
});

// Report download route
Route::get('/reports/download/{filename}', function ($filename) {
    $path = storage_path('app/temp/'.$filename);

    if (! file_exists($path)) {
        abort(404, 'File not found');
    }

    return response()->download($path)->deleteFileAfterSend();
})->middleware(['auth'])->name('reports.download');

require __DIR__.'/auth.php';
