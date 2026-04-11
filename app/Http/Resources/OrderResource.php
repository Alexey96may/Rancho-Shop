<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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

            'customer_name' => $this->customer_name,
            'delivery_address' => $this->delivery_address,

            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,

            'status' => $this->status,

            'discount_total' => $this->discount_total,
            'promo_code_id' => $this->promo_code_id,

            'created_at' => $this->created_at,
            'total_items' => $this->items->sum('quantity'),

            // 👇 связь
            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}
