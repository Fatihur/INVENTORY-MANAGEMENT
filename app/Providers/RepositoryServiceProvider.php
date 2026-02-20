<?php

namespace App\Providers;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockRepositoryInterface;
use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Contracts\Services\QrCodeServiceInterface;
use App\Contracts\Services\RestockRecommendationServiceInterface;
use App\Contracts\Services\ForecastingServiceInterface;
use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Contracts\Services\StockServiceInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\StockRepository;
use App\Repositories\Eloquent\SupplierRepository;
use App\Repositories\Eloquent\PurchaseOrderRepository;
use App\Repositories\Eloquent\StockMovementRepository;
use App\Services\QrCode\QrCodeService;
use App\Services\Restock\RestockRecommendationService;
use App\Services\Forecasting\ForecastingService;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Services\Stock\StockService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(StockRepositoryInterface::class, StockRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, PurchaseOrderRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);

        $this->app->bind(QrCodeServiceInterface::class, QrCodeService::class);
        $this->app->bind(RestockRecommendationServiceInterface::class, RestockRecommendationService::class);
        $this->app->bind(ForecastingServiceInterface::class, ForecastingService::class);
        $this->app->bind(PurchaseOrderServiceInterface::class, PurchaseOrderService::class);
        $this->app->bind(StockServiceInterface::class, StockService::class);
    }
}
