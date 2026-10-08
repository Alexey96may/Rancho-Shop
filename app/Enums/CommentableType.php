<?php

namespace App\Enums;

enum CommentableType: string
{
    case ANIMAL = 'animal';
    case PRODUCT = 'product';
    case PAGE = 'page';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
