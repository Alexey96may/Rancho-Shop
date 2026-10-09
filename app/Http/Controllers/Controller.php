<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeoResource;
use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    protected function seo(
        string $title,
        ?string $description = null,
        ?string $image = null,
        string $robots = 'index, follow',
        ?string $keywords = null,
        ?string $canonical = null
    ): array {
        return [
            'title' => $title,
            'description' => $description ?? 'Натуральные молочные продукты из Крыма.',
            'keywords' => $keywords ?? 'домашнее молоко, крым, творог, купить фермерские продукты',
            'robots' => $robots,
            'canonical' => $canonical ?? url()->current(),
            'image' => $image ?? asset('images/og-default.png'),

            'og_data' => null,
            'json_ld' => null,
        ];
    }

    /**
     * SEO From Model (Page, Product, Animal, …).
     * Or default
     */
    protected function seoFromModel(?Model $model, ?string $fallbackTitle = null): array
    {
        if ($model && $model->relationLoaded('seo') && $model->seo) {
            return (new SeoResource($model->seo))->resolve();
        }

        return $this->seo(
            title: $fallbackTitle
                ?? $model?->name
                ?? $model?->title
                ?? config('app.name', 'Молочная Долина'),
        );
    }
}
