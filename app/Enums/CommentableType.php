<?php

namespace App\Enums;

use App\Models\Animal;
use App\Models\Page;
use App\Models\Product;

enum CommentableType: string
{
    case ANIMAL = 'animal';
    case PRODUCT = 'product';
    case PAGE = 'page';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function modelClass(): string
    {
        return match ($this) {
            self::PRODUCT => Product::class,
            self::ANIMAL => Animal::class,
            self::PAGE => Page::class,
        };
    }
}
