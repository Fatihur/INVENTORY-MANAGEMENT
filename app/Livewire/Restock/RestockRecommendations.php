<?php

namespace App\Livewire\Restock;

use App\Contracts\Services\PurchaseOrderServiceInterface;
use App\Contracts\Services\RestockRecommendationServiceInterface;
use Livewire\Component;

class RestockRecommendations extends Component
{
    public string $priorityFilter = 'all';
    public ?int $selectedSupplier = null;
    public array $selectedItems = [];
    public bool $showGenerateModal = false;

    protected $queryString = ['priorityFilter', 'selectedSupplier'];

    public function getRecommendationsProperty(RestockRecommendationServiceInterface $service)
    {
        $recommendations = $this->selectedSupplier
            ? $service->getBySupplier($this->selectedSupplier)
            : $service->getRecommendations();

        if ($this->priorityFilter !== 'all') {
            $recommendations = $recommendations->where('priority', $this->priorityFilter);
        }

        return $recommendations;
    }

    public function generatePurchaseOrders(PurchaseOrderServiceInterface $poService)
    {
        $selectedRecommendations = $this->getRecommendationsProperty(
            app(RestockRecommendationServiceInterface::class)
        )->whereIn('product.id', $this->selectedItems);

        $pos = $poService->createFromRecommendations($selectedRecommendations, auth()->id());

        $this->selectedItems = [];
        $this->showGenerateModal = false;

        session()->flash('message', $pos->count() . ' Purchase Order(s) created successfully.');
        return redirect()->route('purchase-orders.index');
    }

    public function render()
    {
        return view('livewire.restock.restock-recommendations', [
            'recommendations' => $this->getRecommendationsProperty(
                app(RestockRecommendationServiceInterface::class)
            ),
        ]);
    }
}
