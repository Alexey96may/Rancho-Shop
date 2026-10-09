<?php

namespace App\Traits;

use App\Models\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method MorphOne seo()
 */
trait HasSeoActions
{
    /**
     * Polymorphic SEO relation.
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * Automatically update or create SEO for a model
     */
    public function syncSeo(?array $seoData = null): void
    {
        if (empty($seoData)) {
            return;
        }

        $this->seo()->updateOrCreate([], $seoData);
    }
}
