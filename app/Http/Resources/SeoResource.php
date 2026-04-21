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
        $model = $this->seoable;

        return [
            'id'          => $this->id,
            'title'       => $this->title ?? $model?->name,
            'description' => $this->description ?? $model?->description ?? $model?->bio,
            'keywords'    => $this->keywords,

            'robots'      => $this->is_noindex ? 'noindex, nofollow' : 'index, follow',
            'is_noindex'  => (bool) $this->is_noindex,

            'image'       => $this->getOgImage($model),

            'canonical'   => $this->canonical ?? url()->current(),
            
            'og_data' => [
                'title'       => $this->og_data['title'] ?? $this->title ?? $model?->name,
                'description' => $this->og_data['description'] ?? $this->description,
                'type'        => $this->og_data['type'] ?? 'website',
                'url'         => url()->current(),
                'image'       => $this->getOgImage($model),
            ],

            'json_ld'     => $this->generateJsonLd($model),
        ];
    }

    private function getOgImage($model): ?string
    {
        // 1. If the link in og_data is manually set
        if (!empty($this->og_data['image'])) {
            return $this->og_data['image'];
        }

        // 2. If the model has an avatar (Spatie Media)
        if ($model && method_exists($model, 'getFirstMediaUrl') && $model->getFirstMediaUrl('avatars')) {
            return $model->getFirstMediaUrl('avatars', 'preview');
        }

        // 3. Default ranch stub
        return asset('images/og-default-rancho.jpg');
    }

    /**
     * Generating Schema.org markup
     */
    private function generateJsonLd($model): array
    {
        if (!$model) return [];

        // Basic structure for all (Animal, Product, Page)
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => $this->getSchemaType($model),
            'name'     => $model->name ?? $this->title,
            'description' => $this->description,
            'url'      => url()->current(),
            'image'    => $this->getOgImage($model),
        ];

        // If it's an Animal, add some specifics
        if ($model instanceof \App\Models\Animal) {
            $data['@type'] = 'IndividualProduct'; // Animal on a farm as a unique unit
            $data['category'] = $model->category?->name;
            
            if (!empty($model->features)) {
                $data['additionalProperty'] = collect($model->features)->map(fn($val, $key) => [
                    '@type' => 'PropertyValue',
                    'name' => $key,
                    'value' => $val
                ])->values()->toArray();
            }
        }

        // If this is a Product, add the price
        if ($model instanceof \App\Models\Product) {
            $data['@type'] = 'Product';
            $data['offers'] = [
                '@type' => 'Offer',
                'price' => $model->price, // assume that there is a price in the model
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock',
            ];
        }

        return $data;
    }

    private function getSchemaType($model): string
    {
        return match (true) {
            $model instanceof \App\Models\Product => 'Product',
            $model instanceof \App\Models\Animal  => 'IndividualProduct',
            default => 'WebPage',
        };
    }
}
