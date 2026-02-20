<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new \App\Http\Resources\ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_rate' => $this->tax_rate,
            'discount_percent' => $this->discount_percent,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'notes' => $this->notes,
        ];
    }
}
