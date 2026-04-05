<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'url' => $this->getUrl(),
            'thumbnails' => [
                'original'  => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
                'webp' => $this->hasGeneratedConversion('thumb_webp') ? $this->getUrl('thumb_webp') : null,
                'avif' => $this->hasGeneratedConversion('thumb_avif') ? $this->getUrl('thumb_avif') : null,
            ],
            'previews' => [
                'original'  => $this->hasGeneratedConversion('preview') ? $this->getUrl('preview') : null,
                'webp' => $this->hasGeneratedConversion('preview_webp') ? $this->getUrl('preview_webp') : null,
                'avif' => $this->hasGeneratedConversion('preview_avif') ? $this->getUrl('preview_avif') : null,
            ],

            'responsive' => $this->hasGeneratedConversion('preview') 
                ? $this->getResponsiveImageUrls('preview') 
                : null,

            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'order_column' => $this->order_column,
        ];
    }
}
