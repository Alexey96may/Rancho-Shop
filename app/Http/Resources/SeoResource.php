<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
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
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'keywords'    => $this->keywords,
            'is_noindex'  => (bool) $this->is_noindex,
            
            'og_data' => $this->og_data ?? [
                'title'       => null,
                'description' => null,
                'image'       => null,
                'type'        => 'website',
                'url'         => null,
            ],
        ];
    }
}
