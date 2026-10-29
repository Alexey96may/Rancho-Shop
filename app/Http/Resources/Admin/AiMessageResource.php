<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiMessageResource extends JsonResource
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
            'session_id' => $this->session_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'content' => $this->content,
            'meta' => [
                'model' => $this->meta['model'] ?? null,
                'usage' => $this->meta['usage'] ?? null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
