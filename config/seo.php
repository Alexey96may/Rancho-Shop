<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    |
    | Default values used by the `seo()` helper in the base Controller
    | when a specific description, keywords or image are not provided.
    |
    */

    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Натуральные молочные продукты из Крыма.'
    ),

    'default_keywords' => env(
        'SEO_DEFAULT_KEYWORDS',
        'домашнее молоко, крым, творог, купить фермерские продукты'
    ),

    'default_image' => env(
        'SEO_DEFAULT_IMAGE',
        'images/og-default.png'
    ),
];
