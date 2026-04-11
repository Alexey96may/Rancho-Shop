<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,

            'price_at_purchase' => $this->price_at_purchase,
            'base_price_at_purchase' => $this->base_price_at_purchase,

            'quantity' => $this->quantity,

            // 👇 удобно сразу считать
            'subtotal' => $this->price_at_purchase * $this->quantity,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
