<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'type' => $this->type,

            $this->mergeWhen($request->user()?->is_admin, [
                'sort_order' => $this->sort_order,
                'is_active'  => (bool) $this->is_active,
                'created_at' => $this->created_at?->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ]),
        ];
    }
}
