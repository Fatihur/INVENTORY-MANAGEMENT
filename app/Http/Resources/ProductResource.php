<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'min_stock' => $this->min_stock,
            'max_stock' => $this->max_stock,
            'track_batch' => $this->track_batch,
            'track_serial' => $this->track_serial,
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
