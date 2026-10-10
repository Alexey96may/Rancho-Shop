<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class SeoObserver
{
    public function saved(): void
    {
        Cache::forget('sitemap.xml');
    }

    public function deleted(): void
    {
        Cache::forget('sitemap.xml');
    }
}
