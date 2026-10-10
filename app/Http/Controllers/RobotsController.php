<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /profile',
            'Disallow: /dashboard',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /api',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        if (app()->isProduction()) {
            $lines[] = '';
            $lines[] = 'User-agent: Yandex';
            $lines[] = 'Disallow: /admin';
        } else {
            // On dev/staging — block everything from indexing.
            $lines = [
                'User-agent: *',
                'Disallow: /',
            ];
        }

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }
}
