<?php

namespace App\Contracts\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Supplier;

    public function getActive(): Collection;

    public function getWithPerformanceMetrics(): Collection;
}
