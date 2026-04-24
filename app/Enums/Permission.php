<?php

namespace App\Enums;

enum Permission: string
{
    case MANAGE_PRODUCTS = 'manage-products';
    case MANAGE_ORDERS = 'manage-orders';
    case MANAGE_COMMENTS = 'manage-comments';
    case MANAGE_DELIVERY = 'manage-delivery';
    case MANAGE_ANIMALS = 'manage-animals';
    case MANAGE_USERS = 'manage-users';
    case MANAGE_CATEGORIES = 'manage-categories';
    case MANAGE_CATALOG = 'manage-catalog';
    case MANAGE_PAGES = 'manage-pages';
    case MANAGE_PROMOCODES = 'manage-promocodes';
    case MANAGE_FAQ = 'manage-faq';
    case MANAGE_FEATURES = 'manage-features';
    case MANAGE_SETTINGS = 'manage-settings';
    case MANAGE_NOMENCLATURE = 'manage-nomenclature';

    public function label(): string
    {
        return match ($this) {
            self::MANAGE_PRODUCTS => 'Продукты',
            self::MANAGE_ORDERS => 'Заказы',
            self::MANAGE_COMMENTS => 'Комментарии',
            self::MANAGE_DELIVERY => 'Доставка',
            self::MANAGE_ANIMALS => 'Животные',
            self::MANAGE_USERS => 'Пользователи',
            self::MANAGE_CATEGORIES => 'Категории',
            self::MANAGE_CATALOG => 'Варианты',
            self::MANAGE_PAGES => 'Страницы',
            self::MANAGE_PROMOCODES => 'Промокоды',
            self::MANAGE_FAQ => 'FAQ',
            self::MANAGE_FEATURES => 'Фичи',
            self::MANAGE_SETTINGS => 'Настройки',
            self::MANAGE_NOMENCLATURE => 'Номенклатура',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn ($permission) => [
                'key' => $permission->name,   // MANAGE_USERS
                'value' => $permission->value, // manage-users
                'label' => $permission->label(),
            ])
            ->values()
            ->toArray();
    }

    public function isAdminOnly(): bool
    {
        return match ($this) {
            self::MANAGE_USERS,
            self::MANAGE_SETTINGS => true,
            default => false,
        };
    }
}
