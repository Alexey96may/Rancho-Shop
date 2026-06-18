<?php

namespace App\Enums;

enum AvailabilityType: string
{
    case STOCK = 'stock';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case PREORDER = 'preorder';

    public function label(): string
    {
        return match($this) {
            self::STOCK => 'В наличии',
            self::DAILY => 'Ежедневно',
            self::PREORDER => 'Предзаказ',
            self::WEEKLY => 'Раз в неделю',
        };
    }
}
