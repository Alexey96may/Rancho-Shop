<?php

namespace App\Traits;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasStandardMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('thumb_webp')
            ->fit(Fit::Contain, 300, 300)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('thumb_avif')
            ->fit(Fit::Contain, 300, 300)
            ->format('avif')
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->fit(Fit::Max, 1200, 1200)
            ->optimize()
            ->withResponsiveImages();

        $this->addMediaConversion('preview_webp')
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp');

        $this->addMediaConversion('preview_avif')
            ->fit(Fit::Max, 1200, 1200)
            ->format('avif');
    }
}
