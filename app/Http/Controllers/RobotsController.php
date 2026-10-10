<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $disallow = [
            '/admin',
            '/profile',
            '/dashboard',
            '/cart',
            '/checkout',
            '/api/',
        ];

        if (!app()->isProduction()) {
            return response("User-agent: *\nDisallow: /", 200)
                ->header('Content-Type', 'text/plain');
        }

        $lines = [];

        $lines[] = 'User-agent: *';
        foreach ($disallow as $path) {
            $lines[] = "Disallow: {$path}";
        }

        // Yandex
        $lines[] = '';
        $lines[] = 'User-agent: Yandex';
        foreach ($disallow as $path) {
            $lines[] = "Disallow: {$path}";
        }
        $lines[] = 'Disallow: /*?sort=';
        $lines[] = 'Disallow: /*?page=';
        $lines[] = 'Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term';
        $lines[] = 'Crawl-delay: 1';

        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }
}
