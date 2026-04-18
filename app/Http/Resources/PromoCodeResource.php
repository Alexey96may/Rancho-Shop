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
            'id'               => $this->id,
            'code'             => $this->code,
            'type'             => $this->type,
            'value'            => $this->value,
            'min_order_amount' => $this->min_order_amount,
            'max_discount'     => $this->max_discount,
            'usage_limit'      => $this->usage_limit,
            'used_count'       => $this->used_count,
            'expires_at'       => $this->expires_at?->format('Y-m-d H:i'),
            'expires_human'    => $this->expires_at?->format('d.m.Y'),
            'is_active'        => $this->is_active,
            // Добавим статус валидности для фронта
            'is_valid'         => $this->isValid(),
            'created_at'       => $this->created_at?->format('d.m.Y'),
        ];
    }
}
