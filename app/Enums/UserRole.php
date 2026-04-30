<?php

namespace App\Enums;

enum UserRole: string {
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case MODERATOR = 'moderator';
    case WORKER = 'worker';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Администратор',
            self::CUSTOMER => 'Клиент',
            self::MODERATOR => 'Модератор',
            self::WORKER => 'Сотрудник',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ADMIN => 'red',
            self::MODERATOR => 'purple',
            self::WORKER => 'blue',
            self::CUSTOMER => 'slate',
        };
    }
}