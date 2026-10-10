<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\PageResource;
use Illuminate\Http\Request;

class AdminPageResource extends PageResource
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
            'type' => $this->type,
            'type_label' => $this->type->label(),
            'type_color' => $this->type->color(),

            'is_active' => $this->is_active,
            'show_in_menu' => $this->show_in_menu,
            'menu_order' => $this->menu_order,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'can_delete' => $this->resource->isDeletable(),

            'content' => $this->when($request->routeIs('admin.pages.edit'), $this->content),
        ]);
    }
}
