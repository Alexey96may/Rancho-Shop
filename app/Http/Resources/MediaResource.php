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
        $model = $this->model;

        return [
            'id' => $this->id,
            'url' => $this->getUrl(),
            'thumbnails' => [
                'original'  => ($this->id && $this->hasGeneratedConversion('thumb')) 
                                ? $this->getUrl('thumb') : $model->getFallbackImage(),
                'webp'      => ($this->id && $this->hasGeneratedConversion('thumb_webp')) 
                                ? $this->getUrl('thumb_webp') : $model->getFallbackImage(),
                'avif'      => ($this->id && $this->hasGeneratedConversion('thumb_avif')) 
                                ? $this->getUrl('thumb_avif') : $model->getFallbackImage(),
            ],
            'previews' => [
                'original'  => ($this->id && $this->hasGeneratedConversion('preview')) 
                                ? $this->getUrl('preview') : $model->getFallbackImage(),
                'webp'      => ($this->id && $this->hasGeneratedConversion('preview_webp')) 
                                ? $this->getUrl('preview_webp') : $model->getFallbackImage(),
                'avif'      => ($this->id && $this->hasGeneratedConversion('preview_avif')) 
                                ? $this->getUrl('preview_avif') : $model->getFallbackImage(),
            ],

            'responsive' => $this->hasGeneratedConversion('preview') 
                ? $this->getResponsiveImageUrls('preview') 
                : null,

            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'order_column' => $this->order_column,
        ];
    }

    public static function fallback($model): array
    {
        $fallback = (method_exists($model, 'getFallbackImage')) 
            ? $model->getFallbackImage() 
            : asset('images/no-image.jpg');
        
        return [
            'id'           => null,
            'url'          => $fallback,
            'thumbnails'   => [
                'original' => $fallback,
                'webp'     => $fallback,
                'avif'     => $fallback,
            ],
            'previews'     => [
                'original' => $fallback,
                'webp'     => $fallback,
                'avif'     => $fallback,
            ],
            'responsive'   => null,
            'name'         => 'placeholder',
            'mime_type'    => 'image/jpeg',
            'order_column' => 0,
        ];
    }
}
