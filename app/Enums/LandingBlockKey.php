<?php

namespace App\Enums;

enum LandingBlockKey: string
{
    case VALUES = 'values';
    case ABOUT = 'about';
    case HOW_IT_WORKS = 'how_it_works';

    public function label(): string
    {
        return match($this) {
            self::VALUES => 'Наши ценности',
            self::ABOUT => 'О компании',
            self::HOW_IT_WORKS => 'Как это работает',
        };
    }
}
