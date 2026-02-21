<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalStock = $this->whenLoaded('stocks', fn () => $this->stocks->sum('qty_on_hand'));

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'category' => $this->category,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'min_stock' => $this->min_stock,
            'safety_stock' => $this->safety_stock,
            'target_stock' => $this->target_stock,
            'lead_time_days' => $this->lead_time_days,
            'max_stock' => $this->max_stock,
            'track_batch' => $this->track_batch,
            'track_serial' => $this->track_serial,
            'is_active' => $this->is_active,
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
            'total_stock' => $totalStock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
