<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case DELIVERING = 'delivering';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Новый',
            self::CONFIRMED => 'Подтвержден',
            self::DELIVERING => 'Доставка',
            self::COMPLETED => 'Завершен',
            self::CANCELLED => 'Отменен',
        };
    }
}
