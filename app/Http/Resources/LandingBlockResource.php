<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'key'      => $this->key,
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            
            'content'  => collect($this->content)->map(fn($item) => [
                'title' => $item['title'] ?? '',
                'desc'  => $item['desc'] ?? '',
                'icon'  => $item['icon'] ?? null,
                'step'  => isset($item['step']) ? (int)$item['step'] : null,
            ])->toArray(),
        ];
    }
}
