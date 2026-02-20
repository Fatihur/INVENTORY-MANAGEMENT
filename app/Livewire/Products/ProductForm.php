<?php

namespace App\Livewire\Products;

use App\Contracts\Services\QrCodeServiceInterface;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public string $sku = '';
    public string $name = '';
    public string $description = '';
    public string $unit = 'pcs';
    public string $category = '';
    public int $min_stock = 0;
    public int $safety_stock = 0;
    public ?int $target_stock = null;
    public int $lead_time_days = 7;
    public bool $track_batch = false;
    public bool $is_active = true;

    protected function rules()
    {
        return [
            'sku' => 'required|string|max:50|unique:products,sku' . ($this->product ? ',' . $this->product->id : ''),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'category' => 'nullable|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'safety_stock' => 'required|integer|min:0',
            'target_stock' => 'nullable|integer|min:0',
            'lead_time_days' => 'required|integer|min:1',
            'track_batch' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function mount(?int $product = null)
    {
        if ($product) {
            $this->product = Product::findOrFail($product);
            $this->fill($this->product->toArray());
        }
    }

    public function save(QrCodeServiceInterface $qrService)
    {
        $this->validate();

        $data = [
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'category' => $this->category,
            'min_stock' => $this->min_stock,
            'safety_stock' => $this->safety_stock,
            'target_stock' => $this->target_stock,
            'lead_time_days' => $this->lead_time_days,
            'track_batch' => $this->track_batch,
            'is_active' => $this->is_active,
        ];

        if ($this->product) {
            $this->product->update($data);
            $product = $this->product;
            $message = 'Product updated successfully.';
        } else {
            $product = Product::create($data);
            $qrService->generateForProduct($product);

            foreach (Warehouse::where('is_active', true)->get() as $warehouse) {
                $product->stocks()->create([
                    'warehouse_id' => $warehouse->id,
                    'qty_on_hand' => 0,
                ]);
            }

            $message = 'Product created successfully.';
        }

        $this->dispatch('product-saved', message: $message);
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.product-form');
    }
}
