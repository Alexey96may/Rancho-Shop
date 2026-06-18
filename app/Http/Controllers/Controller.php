<?php

namespace App\Http\Controllers;

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
            'title'       => $title,
            'description' => $description ?? 'Натуральные молочные продукты из Крыма.',
            'keywords'    => $keywords ?? 'домашнее молоко, крым, творог, купить фермерские продукты',
            'robots'      => $robots,
            'canonical'   => $canonical ?? url()->current(),
            'image'       => $image ?? asset('images/og-default.png'),
            
            'og_data'     => null,
            'json_ld'     => null,
        ];
    }
}
