<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method MorphOne seo()
 */
trait HasSeoActions
{
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
