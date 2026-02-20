<?php

namespace App\Livewire\Stock;

use App\Contracts\Repositories\StockRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class StockList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $warehouseId = null;

    public function render(StockRepositoryInterface $stockRepository)
    {
        $stocks = $stockRepository->paginate(15);

        return view('livewire.stock.stock-list', [
            'stocks' => $stocks,
        ]);
    }
}
