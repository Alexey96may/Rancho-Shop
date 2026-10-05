<?php

namespace App\Services;

use Illuminate\Support\Str;

class SlugService
{
    /**
    * Generates a pure slug based on the submitted form fields
    */
    public static function prepare(?string $slug, string $fallbackName): string
    {
        return empty($slug) 
            ? Str::slug($fallbackName) 
            : Str::slug($slug);
    }
}
