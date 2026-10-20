# 🐄 Rancho — интернет-магазин для молочной фермы

**🌐 [English](README.md)** · Русский

Pet-проект онлайн-магазина для небольшой молочной фермы: товары привязаны к конкретным коровам, зоны доставки, многоролевая админка.
Стек: **Laravel 13 + Vue 3 + Inertia.js**. Прод: [milkvalley.org](https://milkvalley.org).

---

## 📸 Демо

![Demo](docs/screenshots/farmshop-demo.gif)

| Главная                                   | Каталог                                       | Животные                                        |
| ----------------------------------------- | --------------------------------------------- | ----------------------------------------------- |
| ![home](docs/screenshots/Rancho_main.jpg) | ![shop](docs/screenshots/Rancho_products.jpg) | ![animals](docs/screenshots/Rancho_animals.jpg) |

| Админка                                     | Профиль пользователя                      |
| ------------------------------------------- | ----------------------------------------- |
| ![admin](docs/screenshots/Rancho_admin.jpg) | ![user](docs/screenshots/Rancho_user.jpg) |

> Живой прод: [https://milkvalley.org](https://milkvalley.org). Локально поднимается за пару минут (см. [Установку](#-установка)).

---

## 🛠 Стек технологий

**Backend**

- PHP 8.3, Laravel 13
- PostgreSQL + **PostGIS** (зоны доставки)
- Laravel Sanctum (API-авторизация для мобильного клиента)
- Laravel Socialite (вход через VK / Google)
- Spatie Laravel Media Library (конверсии `thumb`/`preview` + `webp`/`avif`)
- HTMLPurifier (санитайзинг rich-text контента)
- Tightenco Ziggy (роуты в JS)
- Inertia SSR (server-side rendering)

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

**Интеграции**

- **Telegram-бот** — оповещения о новых пользователях и заказах в группу, со скриншотом карты Яндекса
- **Яндекс Метрика** — счётчик через `YANDEX_METRIKA_ID`
- **Яндекс Статик API** — скриншот карты для Telegram-бота
- **Платежи:** YooKassa (протестировано), Direct / Tinkoff / PayMaster / Fake через единый `PaymentGatewayInterface`

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

- **Telegram-бот** — новые пользователи и заказы в группу (со скриншотом карты Яндекса)
- **Яндекс Метрика** — счётчик на сайте
- Laravel Socialite (VK / Google) — в процессе
- API для Android-клиента (Sanctum) — частично реализовано
- **Платежи:** YooKassa (протестировано), Direct / Tinkoff / PayMaster / Fake через `PaymentGatewayInterface`

### Производительность

- **SSR (Inertia server-side rendering)** — быстрый first paint, лучше для SEO
- **Современные форматы изображений** — AVIF/WebP через `<picture>`, blur-up, responsive images (Spatie Media Library)

---

## ✅ Реализовано

- [x] Чистая архитектура: Controllers, Services, Actions, DTO, Enums, Exceptions, Helpers, Observers, Policies, Providers, Resources
- [x] Роли: `admin`, `customer`, `moderator`, `worker`
- [x] Корзина в UI с серверной валидацией через API
- [x] Умная доставка: админ рисует маршрут, покупатель выбирает точку в радиусе вокруг него
- [x] Умные варианты товаров с создаваемыми величинами измерений и шагом количества
- [x] Единицы измерения с `low_stock_threshold` — оповещение в админке, когда товар заканчивается
- [x] Умное SEO: морфная таблица, ручной ввод, `robots.txt` из админки, `sitemap.xml`
- [x] Мягкое удаление: коровы, товары, отзывы, страницы
- [x] Ролевая система доступа
- [x] **Telegram-бот**: оповещения в группу о новых пользователях и заказах, со скриншотом карты Яндекса
- [x] **Яндекс Метрика** (счётчик через `YANDEX_METRIKA_ID`)
- [x] **Яндекс Статик API** (скриншот карты для Telegram-бота)
- [x] Настройки с кешированием (TTL 24 часа, сброс при сохранении)
- [x] Скидки и промокоды (только в БД и админке)
- [x] Платежи: YooKassa (протестировано), Direct, Tinkoff, Fake — через единый `PaymentGatewayInterface`
- [x] **SSR (server-side rendering в Inertia)**
- [x] **Современные форматы изображений**: AVIF/WebP, конверсии `thumb`/`preview`, responsive images
- [x] Магическая ссылка, временный автоматический аккаунт
- [x] Кнопка «Поделиться»

## 🔮 В планах

- [ ] Перевод на английский.
- [ ] Подписка на животных.
- [ ] Подарки/баллы от животных.
- [ ] Рейтинг животных и продуктов.
- [ ] Корзина в БД?
- [ ] Привязать кнопки «Купить» / «Предзаказ» к графику доставки.
- [ ] Работа с сокетами. Сообщения админу/модератору/работнику.

---

## 🚀 Установка

### Требования

- PHP 8.3+, Composer 2
- Node.js 20+, npm
- PostgreSQL 16+ **с расширением PostGIS**
- **RAM:** минимум 2 ГБ + **4–6 ГБ swap** для `npm run build` (сборка 4785 модулей на 0.9 ГБ RAM падает с `Killed`/exit 137). Для прод-сервера с 1 ГБ RAM swap обязателен.
- (Опционально) Mapbox access token — для карты зон доставки
- (Опционально) ключ Yandex Geocoder — для подсказок адресов
- (Опционально) ключи VK / Google OAuth
- (Опционально) Telegram bot token + chat_id — для оповещений
- (Опционально) Яндекс Статик API key — для скриншота карты в Telegram
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

# 2. Редактируем .env (см. раздел «Переменные окружения»)

# 3. Включаем PostGIS в базе
psql -U postgres -d rancho_db -c "CREATE EXTENSION IF NOT EXISTS postgis;"

# 4. Миграции и сиды
php artisan migrate --seed

# 5. Запускаем всё одной командой
composer dev
```

`composer dev` поднимает `php artisan serve`, `npm run dev`, queue worker и pail (логи).

### Демо-доступы

Создаются `UserSeeder` (в production сидер пропускается). Пароли по умолчанию:

| Роль           | Телефон              | Пароль      |
| -------------- | -------------------- | ----------- |
| **Админ**      | `+7 (999) 111-11-11` | `admin`     |
| **Модератор**  | `+7 (999) 333-33-33` | `moderator` |
| **Работник**   | `+7 (999) 444-44-44` | `worker`    |
| **Покупатель** | `+7 (999) 222-22-22` | `customer`  |

Для админа можно переопределить через `.env`: `ADMIN_EMAIL`, `ADMIN_PHONE`, `ADMIN_PASSWORD`.

### Переменные окружения

Полный список — в `.env.example`. Ключевые:

```env
APP_URL=http://localhost:8000
APP_TIMEZONE_DISPLAY=Europe/Moscow

# Админ (переопределяет хардкод в UserSeeder)
ADMIN_EMAIL=admin@rancho.com
ADMIN_PHONE=+7 (999) 111-11-11
ADMIN_PASSWORD=admin

# База
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rancho_db
DB_USERNAME=postgres
DB_PASSWORD=

# Фронтенд
VITE_MAPBOX_TOKEN=
VITE_YANDEX_GEOCODER_KEY=

# Яндекс Статик API (скриншот карты в Telegram-боте)
YANDEX_STATIC_API_KEY=

# Платежи
PAYMENT_DRIVER=fake            # "fake" | "direct" | "tinkoff" | "yookassa" | "paymaster"
PAYMASTER_MERCHANT_ID=
PAYMASTER_SECRET_KEY=
TINKOFF_TERMINAL_KEY=
TINKOFF_SECRET_KEY=
TINKOFF_API_URL=
YOOKASSA_SHOP_ID=
YOOKASSA_SECRET_KEY=
YOOKASSA_API_URL=

# Inertia SSR
INERTIA_SSR_ENABLED=false
INERTIA_SSR_URL=http://127.0.0.1:13714

# Telegram
TELEGRAM_BOT_TOKEN=
TELEGRAM_ADMIN_CHAT_ID=

# Яндекс Метрика
YANDEX_METRIKA_ID=
```

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

---

## 🖥 SSR (Inertia server-side rendering)

По умолчанию выключен (`INERTIA_SSR_ENABLED=false`). Чтобы включить:

```bash
# 1. Собрать SSR-бандл
npm run build:ssr

# 2. Запустить SSR-сервер
php artisan inertia:start-ssr
```

Проверка:

```bash
curl http://127.0.0.1:13714/health
# {"status":"OK","timestamp":...}
```

В `.env`:

```env
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714
```

На проде SSR запускается через systemd (`milkshop-ssr.service`). При деплое `npm run build:ssr` пересобирает `bootstrap/ssr`, затем сервис рестартится, а `deploy.sh` ждёт ответа `/health` (retry до 30 секунд — SSR стартует ~9 секунд).

---

## 🤖 Telegram-бот

Оповещения о новых пользователях и заказах в группу, со скриншотом карты Яндекса (адрес доставки).

1. Создайте бота через [@BotFather](https://t.me/BotFather), получите `TELEGRAM_BOT_TOKEN`.
2. Добавьте бота в группу, дайте права администратора.
3. Получите `TELEGRAM_ADMIN_CHAT_ID`:
    - через [@getidsbot](https://t.me/getidsbot), или
    - `https://api.telegram.org/bot<TOKEN>/getUpdates` (после сообщения в группу).
4. Получите `YANDEX_STATIC_API_KEY` — ключ [Яндекс Статик API](https://yandex.ru/dev/maps/staticapi/) для скриншота карты.
5. Пропишите в `.env`:

```env
TELEGRAM_BOT_TOKEN=123456:ABC...
TELEGRAM_ADMIN_CHAT_ID=-1001234567890
YANDEX_STATIC_API_KEY=...
```

---

## 📊 Яндекс Метрика

1. Создайте счётчик на [metrika.yandex.ru](https://metrika.yandex.ru/) для `milkvalley.org`.
2. В `.env`:

```env
YANDEX_METRIKA_ID=12345678
```

3. `php artisan config:cache`.
4. Код вставляется в `resources/views/app.blade.php` через `config('services.yandex_metrika.id')`. В Git попадает только `config(...)`, без реального ID.

Для SPA-навигации Inertia используется режим без `ssr:true` — Метрика сама ловит `pushState`. Цели (`reachGoal`) отправляются через `ym(window.ymConfig.id, 'reachGoal', 'purchase')` после успешного заказа.

---

## 🖼 Изображения (Spatie Media Library)

- Коллекции: `avatars` (single), `gallery`, `voice` (single).
- Конверсии: `thumb` (300×300), `preview` (1200×1200 max), плюс `_webp` и `_avif` для обеих.
- Качество: `quality(75)` для webp, `quality(60)` для avif, `optimize()` для jpg.
- Фронт: `AppImage.vue` рендерит `<picture>` с `<source type="image/avif">` и `<source type="image/webp">`, fallback — `previews.original` (preview.jpg, не оригинал).
- Blur-up: `thumb_avif` (5 КБ) как размытая подложка.

Перегенерация конверсий:

```bash
php artisan media-library:regenerate --ids=6,11 --force
```

`--ids=` принимает **`media.id`** (первичный ключ таблицы `media`), а не имя файла.

### Шрифты

Лежат в `public/fonts`. Имена файлов — **в нижнем регистре** (`spectral-bold.ttf`, `nunito-regular.ttf`), т.к. Linux чувствителен к регистру, а в CSS пути `/fonts/spectral-bold.ttf`. Если переименовываете — правьте и CSS (`resources/css/app.css`).

В CSS указывайте только те форматы, которые реально есть (`woff2`/`woff`/`ttf`), иначе Vite предупредит `didn't resolve at build time`.

---

## 🚢 Деплой на VPS

Деплой через GitHub Actions + `deploy.sh` на сервере.

### 1. SSH-ключ для Actions

На сервере:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy-milkshop" -f ~/.ssh/gh_actions_deploy -N ""
cat ~/.ssh/gh_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 2. Секреты в GitHub

Репозиторий → **Settings** → **Secrets and variables** → **Actions**:

| Имя               | Значение                                      |
| ----------------- | --------------------------------------------- |
| `SSH_HOST`        | IP или домен сервера                          |
| `SSH_USER`        | `root` (или deploy-пользователь)              |
| `SSH_PORT`        | `22`                                          |
| `SSH_PRIVATE_KEY` | содержимое `~/.ssh/gh_actions_deploy` целиком |

### 3. `deploy.sh` на сервере

Делает `git pull`, `composer install`, `npm ci` + `build` + `build:ssr` (если фронт менялся), `artisan`-кеши, миграции, рестарт queue/SSR, права на `storage`/`bootstrap/cache`.

Особенности:

- `NODE_OPTIONS="--max-old-space-size=2048"` для сборки.
- Перед сборкой — `sync` + `drop_caches` (освобождает page cache).
- SSR рестартится и ждётся `/health` с retry до 30 секунд (SSR стартует ~9 секунд).
- При `FRONTEND_CHANGED=true` (определяется `dorny/paths-filter` в workflow) собирается фронт и SSR; иначе — пропускается.

### 4. Запуск

GitHub → **Actions** → **Deploy to VSD** → **Run workflow**.

### Требования к серверу

- **RAM:** минимум 2 ГБ + **4–6 ГБ swap**. На 0.9 ГБ RAM сборка 4785 модулей падает с `Killed` (exit 137). Swap обязателен.
- PHP 8.3, Composer, Node.js 20+, PostgreSQL 16 + PostGIS.
- Nginx с `client_max_body_size 50M` (для загрузки фото).
- PHP-FPM: `upload_max_filesize = 50M`, `post_max_size = 52M`.

Создание swap:

```bash
sudo fallocate -l 4G /swapfile2
sudo chmod 600 /swapfile2
sudo mkswap /swapfile2
sudo swapon /swapfile2
echo '/swapfile2 none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## Автор

**Алексей** — Fullstack-разработчик
Laravel · Vue.js · TypeScript · PostgreSQL
