<?php

namespace App\Enums;

enum PromoCodeType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match($this) {
            self::PERCENT => 'Процент',
            self::FIXED => 'Фиксированная сумма',
        };
    }

    public function symbol(): string
    {
        return match($this) {
            self::PERCENT => '%',
            self::FIXED => '₽',
        };
    }
}
