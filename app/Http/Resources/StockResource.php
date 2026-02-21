<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $qtyOnHand = (int) $this->qty_on_hand;
        $qtyReserved = (int) $this->qty_reserved;

        return [
            'id' => $this->id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'qty_on_hand' => $qtyOnHand,
            'qty_reserved' => $qtyReserved,
            'qty_available' => $qtyOnHand - $qtyReserved,
            'avg_cost' => $this->avg_cost,
            'last_movement_at' => $this->last_movement_at,
        ];
    }
}
