<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
            'credit_limit' => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'addresses' => CustomerAddressResource::collection($this->whenLoaded('addresses')),
            'sales_orders' => SalesOrderResource::collection($this->whenLoaded('salesOrders')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
