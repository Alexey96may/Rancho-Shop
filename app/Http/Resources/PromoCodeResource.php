<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code'  => $this->code,
            'type'   => $this->type?->value ?? 'fixed', 
            'symbol' => $this->type?->symbol() ?? '₽',
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount,
            'max_discount'     => $this->max_discount,
        ];
    }
}
