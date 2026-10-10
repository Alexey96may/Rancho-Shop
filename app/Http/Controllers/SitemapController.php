<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = cache()->remember('sitemap.xml', now()->addHour(), function () {
            return $this->build()->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function build(): Sitemap
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('home'))->setPriority(1.0)->setChangeFrequency('daily'))
            ->add(Url::create(route('catalog.index'))->setPriority(0.9))
            ->add(Url::create(route('animals.index'))->setPriority(0.8))
            ->add(Url::create(route('about'))->setPriority(0.5))
            ->add(Url::create(route('delivery'))->setPriority(0.5));

        // Products
        Product::active()
            ->whereDoesntHave('seo', fn ($q) => $q->where('is_noindex', true))
            ->get()
            ->each(function (Product $product) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('catalog.show', $product->slug))
                        ->setLastModificationDate($product->updated_at)
                        ->setPriority(0.8)
                        ->setChangeFrequency('weekly')
                );
            });

        // Animals
        Animal::active()
            ->whereDoesntHave('seo', fn ($q) => $q->where('is_noindex', true))
            ->get()
            ->each(function (Animal $animal) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('animals.show', $animal->slug))
                        ->setLastModificationDate($animal->updated_at)
                        ->setPriority(0.6)
                );
            });

        // CMS
        Page::where('is_active', true)
            ->whereDoesntHave('seo', fn ($q) => $q->where('is_noindex', true))
            ->get()
            ->each(function (Page $page) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('pages.show', $page->slug))
                        ->setLastModificationDate($page->updated_at)
                        ->setPriority(0.4)
                );
            });

        return $sitemap;
    }
}
