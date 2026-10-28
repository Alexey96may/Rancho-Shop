<?php

namespace App\Services;

use App\Http\Resources\SeoResource;
use Illuminate\Database\Eloquent\Model;

class SeoService
{
    public function make(
        string $title,
        ?string $description = null,
        ?string $image = null,
        string $robots = 'index, follow',
        ?string $keywords = null,
        ?string $canonical = null
    ): array {
        return [
            'title' => $title,
            'description' => $description ?? config('seo.default_description'),
            'keywords' => $keywords ?? config('seo.default_keywords'),
            'robots' => $robots,
            'canonical' => $canonical ?? url()->current(),
            'image' => $image ?? asset(config('seo.default_image')),
            'og_data' => null,
            'json_ld' => null,
        ];
    }

    public function fromModel(?Model $model, ?string $fallbackTitle = null): array
    {
        if ($model && $model->relationLoaded('seo') && $model->seo) {
            return (new SeoResource($model->seo))->resolve();
        }

        return $this->make(
            title: $fallbackTitle
                ?? $model?->name
                ?? $model?->title
                ?? config('app.name', 'Молочная Долина'),
        );
    }
}
