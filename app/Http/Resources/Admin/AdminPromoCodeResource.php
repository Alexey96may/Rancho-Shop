<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\PromoCodeResource;

class AdminPromoCodeResource extends PromoCodeResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'id'           => $this->id,
            'description'  => $this->description ?? '',
            'type_label'   => $this->type?->label() ?? 'Неизвестно',
            'usage_limit'  => $this->usage_limit,
            'used_count'   => $this->used_count,
            'expires_at'   => $this->expires_at?->toIso8601String(),
            'is_active'    => $this->is_active,
            'is_valid'     => $this->isValid(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'usage_percent' => $this->usage_limit > 0 
                ? round(($this->used_count / $this->usage_limit) * 100) 
                : 0,
            'status' => [
                'value' => $this->status,
                'label' => match($this->status) {
                    'active'   => 'Активен',
                    'expired'  => 'Истек',
                    'depleted' => 'Исчерпан',
                    'inactive' => 'Отключен',
                    default    => 'Неизвестно',
                },
                'color' => match($this->status) {
                    'active'   => 'green',
                    'expired'  => 'red',
                    'depleted' => 'orange',
                    'inactive' => 'slate',
                    default    => 'gray',
                },
            ],
        ]);
    }
}
