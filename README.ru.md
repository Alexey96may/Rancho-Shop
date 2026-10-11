# 🐄 Rancho — интернет-магазин для молочной фермы

**🌐 [English](README.md)** · Русский

Pet-проект онлайн-магазина для небольшой молочной фермы: товары привязаны к конкретным коровам, зоны доставки на PostGIS, многоролевая админка.
Стек: **Laravel 13 + Vue 3 + Inertia.js**.

---

## 📸 Демо

![Demo](docs/screenshots/farmshop-demo.gif)

| Главная                                   | Каталог                                       | Животные                                        |
| ----------------------------------------- | --------------------------------------------- | ----------------------------------------------- |
| ![home](docs/screenshots/Rancho_main.jpg) | ![shop](docs/screenshots/Rancho_products.jpg) | ![animals](docs/screenshots/Rancho_animals.jpg) |

| Админка                                     | Профиль пользователя                      |
| ------------------------------------------- | ----------------------------------------- |
| ![admin](docs/screenshots/Rancho_admin.jpg) | ![user](docs/screenshots/Rancho_user.jpg) |

> Живого демо пока нет — проект поднимается локально за пару минут (см. [Установку](#-установка)).

---

## 🛠 Стек технологий

**Backend**

- PHP 8.3, Laravel 13
- PostgreSQL + **PostGIS** (зоны доставки)
- Laravel Sanctum (API-авторизация для мобильного клиента)
- Laravel Socialite (вход через VK / Google)
- Spatie Laravel Media Library
- HTMLPurifier (санитайзинг rich-text контента)
- Tightenco Ziggy (роуты в JS)

**Архитектура**

- Controllers · Services · **Actions · DTO · Enums · Exceptions · Helpers**
- Observers · Policies · Providers · **API Resources**
- Роли: `admin`, `moderator`, `worker`, `customer`
- Мягкое удаление для коров, товаров, отзывов, страниц

**Frontend**

- Vue 3 (Composition API) + TypeScript
- Inertia.js 2
- Vite 8
- Tailwind CSS + Typography plugin
- **Pinia** + persist (корзина и настройки в localStorage)
- **TipTap** — редактор rich-text в админке
- **ApexCharts** (`vue3-apexcharts`) — аналитика в админке
- **Mapbox GL + Turf.js** — карта зон доставки и гео-расчёты
- **v-calendar** + date-fns — выбор дат
- Swiper · Howler · VueUse · vuedraggable
- Headless UI · Heroicons · Lucide · Popper
- browser-image-compression — сжатие изображений на клиенте
- Lodash, lodash.debounce

**Качество**

- PHPUnit 12 (Unit + Feature)
- Laravel Pint (PSR-12)
- Laravel Pail (логи в dev)
- Vitest + @vue/test-utils + jsdom
- ESLint 9 (Vue + TS) + Prettier (Tailwind и сортировка импортов)
- barryvdh/laravel-ide-helper

---

## ✨ Возможности

### Витрина

- Лендинг с настраиваемыми блоками (hero, баннеры, FAQ, избранные товары)
- Каталог товаров с фильтрами и пагинацией
- Страница товара привязана к конкретной корове (или общая, без коровы)
- Раздел «Животные» — у каждой коровы профиль, биография, аудио, галерея, родословная
- Корзина в Pinia (localStorage), цены пересверяются с сервером
- Оформление заказа с промокодами и скидками
- Отзывы (в планах: вход через VK / Google)
- Статичные страницы из БД (О компании, Контакты, Оферта, 404)
- Страница контактов с картой Mapbox

### Доставка

- Зоны доставки хранятся как PostGIS-полигоны
- Вне зоны → доставки нет; рядом с зоной → надбавка
- Выбор адреса с Mapbox + проверка расстояния через Turf.js
- **Умная доставка по маршруту**: админ задаёт маршрут развоза, покупатель выбирает точку в радиусе вокруг маршрута

### Админка

- Товары, коровы, категории, заказы, отзывы, страницы, настройки
- Многоролевой доступ: `admin`, `moderator`, `worker`
- Редактор TipTap для контента
- Дашборд с ApexCharts
- Галереи изображений с drag & drop загрузкой (Spatie Media Library)
- **Единицы измерения с `low_stock_threshold`** — в админке визуальное оповещение, когда товар заканчивается

### SEO

- Морфная таблица SEO с ручным вводом для каждой модели
- Автогенерация OG / Twitter мета
- `robots.txt` редактируется из админки
- `sitemap.xml` — с приоритетами и фильтрацией `is_noindex`
- Canonical URL
- Полное покрытие ARIA (roles, `aria-label`, `aria-live`, `aria-busy`, …)

### Интеграции

- Laravel Socialite (VK / Google) — в процессе
- API для Android-клиента (Sanctum) — частично реализовано
- **Платежи:** протестировано с YooKassa; также поддерживаются Direct / Tinkoff / PayMaster / Fake через единый `PaymentGatewayInterface`

---

## ✅ Реализовано

- [x] Чистая архитектура: Controllers, Services, Actions, DTO, Enums, Exceptions, Helpers, Observers, Policies, Providers, Resources
- [x] Роли: `admin`, `customer`, `moderator`, `worker`
- [x] Корзина в UI с серверной валидацией через API
- [x] Умная доставка: админ рисует маршрут, покупатель выбирает точку в радиусе вокруг него
- [x] Единицы измерения с `low_stock_threshold` — оповещение в админке, когда товар заканчивается
- [x] Умное SEO: морфная таблица, ручной ввод, `robots.txt` из админки, `sitemap.xml`
- [x] Мягкое удаление: коровы, товары, отзывы, страницы
- [x] Ролевая система доступа
- [x] Настройки с кешированием (TTL 24 часа, сброс при сохранении)
- [x] Скидки и промокоды (только в БД и админке)
- [x] Платежи: YooKassa (протестировано), Direct, Tinkoff, Fake — через единый `PaymentGatewayInterface`

## 🔮 В планах

- [ ] Привязать кнопки «Купить» / «Предзаказ» к графику доставки
- [ ] SSR (server-side rendering в Inertia)
- [ ] Промокоды в платежах и публичном UI
- [ ] Скелетоны для карточек

Полный список → [docs/ROADMAP.md](docs/ROADMAP.md)

---

## 🚀 Установка

### Требования

- PHP 8.3+, Composer 2
- Node.js 20+, npm
- PostgreSQL 16+ **с расширением PostGIS**
- (Опционально) Mapbox access token — для карты зон доставки
- (Опционально) ключ Yandex Geocoder — для подсказок адресов
- (Опционально) ключи VK / Google OAuth
- (Опционально) Mailtrap или `MAIL_MAILER=log` для локальных писем

### Установка

```bash
# 1. Клонируем и ставим зависимости
git clone https://github.com/Alexey96may/Rancho-Shop.git
cd Rancho-Shop

composer install
npm install

cp .env.example .env
php artisan key:generate

# 2. Редактируем .env:
#   APP_URL=http://localhost:8000

#   DB_CONNECTION=pgsql
#   DB_HOST=127.0.0.1
#   DB_PORT=5432
#   DB_DATABASE=rancho_db
#   DB_USERNAME=postgres
#   DB_PASSWORD=your_password

#   VITE_MAPBOX_TOKEN=             # карта зон доставки
#   VITE_YANDEX_GEOCODER_KEY=      # подсказки адресов

#   PAYMENT_DRIVER=fake            # "fake" | "direct" | "tinkoff" | "yookassa" | "paymaster"

# 3. Включаем PostGIS в базе
psql -U postgres -d rancho_db -c "CREATE EXTENSION IF NOT EXISTS postgis;"

# 4. Миграции и сиды
php artisan migrate --seed

# 5. Запускаем всё одной командой
composer dev
```

### Демо-доступы

| Роль           | Email                  | Пароль      |
| -------------- | ---------------------- | ----------- |
| **Админ**      | `admin@rancho.com`     | `admin`     |
| **Модератор**  | `moderator@rancho.com` | `moderator` |
| **Работник**   | `worker@rancho.com`    | `worker`    |
| **Покупатель** | `customer@rancho.com`  | `customer`  |

### Платёжные драйверы

`PAYMENT_DRIVER` принимает:

| Значение    | Описание                                                     | Обязательные переменные                                         |
| ----------- | ------------------------------------------------------------ | --------------------------------------------------------------- |
| `fake`      | Мгновенная симуляция оплаты (рекомендуется для локалки)      | —                                                               |
| `direct`    | Ручной перевод по СБП / карте — реквизиты показываются юзеру | `PAYMENT_DIRECT_*`                                              |
| `tinkoff`   | Эквайринг Тинькофф                                           | `TINKOFF_TERMINAL_KEY`, `TINKOFF_SECRET_KEY`, `TINKOFF_API_URL` |
| `yookassa`  | ЮKassa                                                       | `YOOKASSA_SHOP_ID`, `YOOKASSA_SECRET_KEY`, `YOOKASSA_API_URL`   |
| `paymaster` | PayMaster                                                    | `PAYMASTER_MERCHANT_ID`, `PAYMASTER_SECRET_KEY`                 |

Для локальной разработки используй `fake` — реальные ключи не нужны.

## Автор

**Алексей** — Fullstack-разработчик
Laravel · Vue.js · TypeScript · PostgreSQL
