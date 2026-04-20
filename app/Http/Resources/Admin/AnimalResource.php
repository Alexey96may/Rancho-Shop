<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\AnimalResource as UserAnimalResource;

class AnimalResource extends UserAnimalResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'parent_id' => $this->parent_id,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
            'media' => MediaResource::collection($this->getMedia('avatars')),

            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'updated_at' => $this->updated_at->format('d.m.Y H:i'),
        ]);
    }
}
