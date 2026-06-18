<?php

namespace App\Enums;

enum PageType: string
{
    case DEFAULT = 'default';
    case SYSTEM = 'system';
    case CONTACTS = 'contacts'; 
    case HOME = 'home';

    public function label(): string
    {
        return match($this) {
            self::DEFAULT => 'Обычная',
            self::SYSTEM  => 'Системная',
            self::CONTACTS => 'Контакты',
            self::HOME     => 'Главная',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SYSTEM  => 'text-red-400 bg-red-400/10',
            self::HOME     => 'text-blue-400 bg-blue-400/10',
            default       => 'text-slate-400 bg-slate-400/10',
        };
    }
}
