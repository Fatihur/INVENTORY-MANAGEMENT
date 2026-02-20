<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'so_number' => $this->so_number,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'warehouse' => new \App\Http\Resources\WarehouseResource($this->whenLoaded('warehouse')),
            'status' => $this->status,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
