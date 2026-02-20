<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'qty' => $this->qty,
            'reserved_qty' => $this->reserved_qty,
            'available_qty' => $this->qty - $this->reserved_qty,
        ];
    }
}
