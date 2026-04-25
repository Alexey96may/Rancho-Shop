<?php

namespace App\Enums;

enum CommentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case HIDDEN = 'hidden';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Новый',
            self::APPROVED => 'Одобрен',
            self::HIDDEN => 'Скрыт',
        };
    }
}