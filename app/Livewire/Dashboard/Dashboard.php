<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_products' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'pending_pos' => PurchaseOrder::whereIn('status', ['draft', 'sent', 'approved'])->count(),
        ];

        $recentMovements = StockMovement::with(['product', 'warehouse'])
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.dashboard.dashboard', [
            'stats' => $stats,
            'recentMovements' => $recentMovements,
        ]);
    }
}
