<?php

namespace App\Http\Resources;

use App\Models\Animal;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{
    /**
     * Cache for og:image to avoid fetching the media twice.
     */
    private ?string $ogImage = null;

    public function toArray(Request $request): array
    {
        /** @var Model|null $model */
        $model = $this->seoable;

        $this->ogImage = $this->resolveOgImage($model);

        $title = $this->title ?? $this->modelTitle($model);
        $description = $this->description ?? $this->modelDescription($model);

        return [
            'id' => $this->id,
            'title' => $title,
            'description' => $description,
            'keywords' => $this->keywords,

            'robots' => $this->is_noindex ? 'noindex, nofollow' : 'index, follow',
            'is_noindex' => (bool) $this->is_noindex,

            'image' => $this->ogImage,

            'canonical' => $this->canonical ?: null,

            'og_data' => [
                'title' => data_get($this->og_data, 'title') ?? $title,
                'description' => data_get($this->og_data, 'description') ?? $description,
                'type' => data_get($this->og_data, 'type') ?? 'website',
                'url' => url()->current(),
                'image' => $this->ogImage,
            ],

            'json_ld' => $this->generateJsonLd($model) ?: null,
        ];
    }

    // ============================================================
    // Model: title and description with type-based fallbacks
    // ============================================================

    private function modelTitle(?Model $model): ?string
    {
        return match (true) {
            $model instanceof Page => $model->title ?? $model->name,
            $model instanceof Product => $model->name,
            $model instanceof Animal => $model->name,
            default => $model?->name,
        };
    }

    private function modelDescription(?Model $model): ?string
    {
        return match (true) {
            $model instanceof Page => $model->excerpt ?? $model->description,
            $model instanceof Product => $model->short_description ?? $model->description,
            $model instanceof Animal => $model->bio ?? $model->description,
            default => $model?->description,
        };
    }

    // ============================================================
    // OG Image
    // ============================================================

    private function resolveOgImage(?Model $model): string
    {
        // 1. Manual image from og_data
        $manual = data_get($this->og_data, 'image');
        if (!empty($manual)) {
            return $manual;
        }
        // 2. The first image from a suitable media collection
        if ($model && method_exists($model, 'getFirstMediaUrl')) {
            $collection = $this->mediaCollectionFor($model);

            if ($collection) {
                $url = $model->getFirstMediaUrl($collection, 'preview')
                    ?: $model->getFirstMediaUrl($collection);

                if ($url) {
                    return $url;
                }
            }
        }

        return asset('images/og-default-rancho.jpg');
    }

    /**
     * Name of the media collection for a specific model.
     */
    private function mediaCollectionFor(Model $model): ?string
    {
        return match (true) {
            $model instanceof Product => 'images',
            $model instanceof Animal => 'avatars',
            $model instanceof Page => 'images',
            default => null,
        };
    }

    // ============================================================
    // Schema.org (json_ld)
    // ============================================================

    private function generateJsonLd(?Model $model): array
    {
        if (!$model) {
            return [];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => $this->getSchemaType($model),
            'name' => $this->modelTitle($model) ?? $this->title,
            'description' => $this->description ?? $this->modelDescription($model),
            'url' => url()->current(),
            'image' => $this->ogImage,
        ];

        if ($model instanceof Animal) {
            $data['category'] = $model->category?->name;

            if (!empty($model->features)) {
                $data['additionalProperty'] = collect($model->features)
                    ->map(fn ($val, $key) => [
                        '@type' => 'PropertyValue',
                        'name' => $key,
                        'value' => $val,
                    ])
                    ->values()
                    ->toArray();
            }
        }

        if ($model instanceof Product) {
            $variant = $model->defaultVariant ?? $model->variants->first();

            // price →  price In Rubles
            $priceInRubles = $variant?->price
                ? round($variant->price / 100, 2)
                : null;

            $inStock = $variant && (float) $variant->stock > 0;

            $data['offers'] = array_filter([
                '@type' => 'Offer',
                'price' => $priceInRubles,
                'priceCurrency' => 'RUB',
                'availability' => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => url()->current(),
            ], fn ($v) => $v !== null);
        }

        return $data;
    }

    private function getSchemaType(?Model $model): string
    {
        return match (true) {
            $model instanceof Product => 'Product',
            $model instanceof Animal => 'IndividualProduct',
            default => 'WebPage',
        };
    }
}
