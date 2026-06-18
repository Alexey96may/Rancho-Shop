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

            // Customer
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_comment' => $this->customer_comment,

            // Delivery
            'is_pickup' => (bool) $this->is_pickup,
            'delivery_address' => $this->delivery_address,
            'delivery_lat' => $this->delivery_lat,
            'delivery_lng' => $this->delivery_lng,
            'delivery_validated' => (bool) $this->delivery_validated,

            // Financial
            'discount_total' => $this->discount_total,
            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,

            'status' => $this->status,
            'status_label' => $this->status->label(),

            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'updated_at' => $this->updated_at,

            // Relations
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => $this->when($this->relationLoaded('user') && $this->user, function() {
                return new UserResource($this->user);
            }),
            
            'promo_code' => new PromoCodeResource($this->whenLoaded('promoCode')),
        ];
    }
}
