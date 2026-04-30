<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?? 'Не указан',
            'role' => [
                'value' => $this->role->value,
                'label' => $this->role->label(),
                'color' => $this->role->color(),
            ],
            'avatar' => $this->avatar_url,
            'is_admin' => $this->isAdmin(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
