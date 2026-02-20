<?php

namespace App\Livewire\Products;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $status = '';
    public int $perPage = 10;

    // Delete modal
    public bool $showDeleteModal = false;
    public ?int $deleteProductId = null;
    public string $deleteProductName = '';

    protected $queryString = ['search', 'category', 'status'];

    protected $listeners = ['productUpdated' => '$refresh'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->deleteProductId = $productId;
            $this->deleteProductName = $product->name;
            $this->showDeleteModal = true;
        }
    }

    public function delete(): void
    {
        if (!$this->deleteProductId) {
            return;
        }

        try {
            $product = Product::find($this->deleteProductId);
            if ($product) {
                $product->delete();
                $this->dispatch('toast', [
                    'message' => "Product '{$this->deleteProductName}' deleted successfully",
                    'type' => 'success'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'message' => 'Failed to delete product: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }

        $this->showDeleteModal = false;
        $this->deleteProductId = null;
        $this->deleteProductName = '';
    }

    public function render(ProductRepositoryInterface $productRepository)
    {
        $filters = [
            'search' => $this->search,
            'category' => $this->category,
            'status' => $this->status,
            'per_page' => $this->perPage,
        ];

        $products = $productRepository->getWithStocks($filters);

        // Get statistics
        $stats = $this->getStats();

        return view('livewire.products.product-list', [
            'products' => $products,
            'totalProducts' => $stats['total'],
            'activeProducts' => $stats['active'],
            'lowStockCount' => $stats['lowStock'],
            'outOfStockCount' => $stats['outOfStock'],
        ]);
    }

    private function getStats(): array
    {
        $query = Product::query();

        // Apply search filter to stats if present
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        $total = $query->count();
        $active = (clone $query)->where('is_active', true)->count();

        // Low stock and out of stock counts
        $lowStock = (clone $query)
            ->where(function ($q) {
                $q->whereRaw('COALESCE((SELECT SUM(qty_on_hand) FROM stocks WHERE stocks.product_id = products.id), 0) <= min_stock')
                  ->whereRaw('COALESCE((SELECT SUM(qty_on_hand) FROM stocks WHERE stocks.product_id = products.id), 0) > 0')
                  ->orWhereDoesntHave('stocks');
            })
            ->count();

        $outOfStock = (clone $query)
            ->whereRaw('COALESCE((SELECT SUM(qty_on_hand) FROM stocks WHERE stocks.product_id = products.id), 0) <= 0')
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
        ];
    }
}
