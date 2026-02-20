<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface ForecastingServiceInterface
{
    public function calculateMovingAverage(int $productId, int $days = 7): float;

    public function predictStockoutDate(int $productId): ?\DateTime;

    public function getDemandTrend(int $productId, int $weeks = 12): Collection;

    public function getFastMovingItems(int $limit = 10): Collection;

    public function getSlowMovingItems(int $limit = 10): Collection;
}
