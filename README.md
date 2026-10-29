# MilkValley — online store for a dairy farm

**🌐 Русский** · [English](README.ru.md)

A pet project: an online store for a small dairy farm — products tied to specific cows, delivery zones, multi-role admin panel.
Stack: **Laravel 13 + Vue 3 + Inertia.js**. Production: [milkvalley.org](https://milkvalley.org).

---

## 📸 Demo

![Demo](docs/screenshots/farmshop-demo.gif)

| Home                                      | Catalog                                       | Animals                                         |
| ----------------------------------------- | --------------------------------------------- | ----------------------------------------------- |
| ![home](docs/screenshots/Rancho_main.jpg) | ![shop](docs/screenshots/Rancho_products.jpg) | ![animals](docs/screenshots/Rancho_animals.jpg) |

| Admin                                       | User profile                              |
| ------------------------------------------- | ----------------------------------------- |
| ![admin](docs/screenshots/Rancho_admin.jpg) | ![user](docs/screenshots/Rancho_user.jpg) |

> Live: [https://milkvalley.org](https://milkvalley.org). Local setup takes a couple of minutes (see [Installation](#-installation)).

---

## 🛠 Tech stack

**Backend**

- PHP 8.3, Laravel 13
- PostgreSQL (delivery zones stored in settings, geo calculations in PHP: Haversine, point-to-segment distance)
- Laravel Sanctum (API auth for mobile client)
- Laravel Socialite (VK / Google login)
- Spatie Laravel Media Library (`thumb`/`preview` conversions + `webp`/`avif`)
- HTMLPurifier (rich-text sanitizing)
- Tightenco Ziggy (routes in JS)
- Inertia SSR (server-side rendering)

**Architecture**

- Controllers · Services · **Actions · DTO · Enums · Exceptions · Helpers**
- Observers · Policies · Providers · **API Resources**
- Roles: `admin`, `moderator`, `worker`, `customer`
- Soft deletes for cows, products, reviews, pages

**Frontend**

- Vue 3 (Composition API) + TypeScript
- Inertia.js 2
- Vite 8
- Tailwind CSS + Typography plugin
- **Pinia** + persist (cart and settings in localStorage)
- **TipTap** — rich-text editor in admin
- **ApexCharts** (`vue3-apexcharts`) — admin analytics
- **Mapbox GL + Turf.js** — delivery zone map and geo calculations
- Swiper · Howler · VueUse · vuedraggable
- Headless UI · Heroicons · Lucide · Popper
- browser-image-compression — client-side image compression
- Lodash, lodash.debounce

**Integrations**

- **Telegram bot** — notifications about new users and orders to a group, with a Yandex Maps screenshot
- **Yandex Metrika** — counter via `YANDEX_METRIKA_ID`
- **Yandex Static API** — map screenshot for the Telegram bot
- **Payments:** YooKassa (tested), Direct / Tinkoff / PayMaster / Fake via a single `PaymentGatewayInterface`
- **Email notifications** — admin and customer emails via SMTP (new orders, status changes, back-in-stock). Queues, error logging, duplicate-send protection.
- **AI assistant (GigaChat)** — on-site chat widget: consultations on products, prices, stock, animals and delivery. Function calling (tools), pre-flight calls for Lite models, escalation to a manager with contact capture, admin notifications via Telegram and email.

**Quality**

- PHPUnit 12 (Unit + Feature)
- Laravel Pint (PSR-12)
- Laravel Pail (dev logs)
- Vitest + @vue/test-utils + jsdom
- ESLint 9 (Vue + TS) + Prettier (Tailwind and import sorting)
- barryvdh/laravel-ide-helper

---

## ✨ Features

### Storefront

- Landing page with configurable blocks (hero, banners, FAQ, featured products)
- Product catalog with filters and pagination
- Product page tied to a specific cow (or generic, without a cow)
- "Animals" section — each cow has a profile, bio, audio, gallery, pedigree
- Cart in Pinia (localStorage), prices re-validated against the server
- Checkout with promo codes and discounts
- Reviews (planned: VK / Google login)
- Static pages from the DB (About, Contacts, Offer, 404)
- Contacts page with Mapbox map
- **AI assistant** — chat widget (bubble + modal) answering questions about products, prices, stock, animals and delivery. Pulls data from the DB via tools, does not hallucinate. On "call a human" — asks for phone/email and notifies the admin.

### Delivery

- Delivery zones stored in settings (JSON), zone check in PHP
- Outside zone → no delivery; near zone → surcharge
- Address selection with Mapbox + distance check via Turf.js (frontend) and Haversine (backend)
- **Smart route-based delivery**: admin draws a route, customer picks a point within a radius around it

### Admin

- Products, cows, categories, orders, reviews, pages, settings
- Multi-role access: `admin`, `moderator`, `worker`
- TipTap editor for content
- Dashboard with ApexCharts
- Image galleries with drag & drop upload (Spatie Media Library)
- **Units with `low_stock_threshold`** — visual alert in admin when a product runs low

### SEO

- Morphological SEO table with manual input per model
- Auto-generated OG / Twitter meta
- `robots.txt` editable from admin
- `sitemap.xml` — with priorities and `is_noindex` filtering
- Canonical URL
- Full ARIA coverage (roles, `aria-label`, `aria-live`, `aria-busy`, …)

### Integrations

- **Telegram bot** — new users and orders to a group (with a Yandex Maps screenshot)
- **Email notifications** — new orders and order status changes (admin and customer), back-in-stock notifications by subscription. SMTP, queues, error logging
- **Yandex Metrika** — site counter
- API for the Android client (Sanctum) — partially implemented
- **Payments:** YooKassa (tested), Direct, Tinkoff, PayMaster, Fake via `PaymentGatewayInterface`
- **AI assistant (GigaChat)** — see [AI assistant](#-ai-assistant) below

### Performance

- **SSR (Inertia server-side rendering)** — fast first paint, better SEO
- **Modern image formats** — AVIF/WebP via `<picture>`, blur-up, responsive images (Spatie Media Library)

---

## ✅ Implemented

- [x] Clean architecture: Controllers, Services, Actions, DTO, Enums, Exceptions, Helpers, Observers, Policies, Providers, Resources
- [x] Roles: `admin`, `customer`, `moderator`, `worker`
- [x] UI cart with server-side validation via API
- [x] Smart delivery: admin draws a route, customer picks a point within a radius
- [x] Smart product variants with custom units and quantity steps
- [x] Units with `low_stock_threshold` — admin alert when a product runs low
- [x] Smart SEO: morphological table, manual input, `robots.txt` from admin, `sitemap.xml`
- [x] Soft deletes: cows, products, reviews, pages
- [x] Role-based access system
- [x] **Telegram bot**: new users and orders to a group, with a Yandex Maps screenshot
- [x] **Yandex Metrika** (counter via `YANDEX_METRIKA_ID`)
- [x] **Yandex Static API** (map screenshot for the Telegram bot)
- [x] Settings with caching (TTL 24h, flushed on save)
- [x] Discounts and promo codes (DB + admin only)
- [x] Payments: YooKassa (tested), Direct, Tinkoff, Fake via `PaymentGatewayInterface`
- [x] **SSR (Inertia server-side rendering)**
- [x] **Modern image formats**: AVIF/WebP, `thumb`/`preview` conversions, responsive images
- [x] Magic link, temporary auto-account
- [x] "Share" button
- [x] **Email notifications**: new orders and status changes (admin and customer), back-in-stock by subscription. Queues, logging, duplicate-send protection
- [x] **AI assistant on GigaChat**: chat widget with dialog history (`ai_messages`), function calling (`get_product_price`, `get_animal_info`, `get_delivery_info`), pre-flight calls for Lite models, dynamic product/animal names from the DB (5 min cache), correct per-unit prices (`price_per_unit` + `step`), escalation to a manager with phone/email recognition, admin notifications via Telegram and email (with name, phone, email of the authenticated user), Honeypot + throttle on the API route

## 🔮 Roadmap

- [ ] English translation.
- [ ] Animal subscriptions.
- [ ] Gifts/points from animals.
- [ ] Animal and product ratings.
- [ ] Tie "Buy" / "Pre-order" buttons to the delivery schedule.
- [ ] Sockets. Messages to admin/moderator/worker.
- [ ] **Redis** — move settings cache, AI name cache (`ai_known_products`, `ai_known_animals`) and sessions from files/DB to Redis for speed and a single store. Queues (`queue:work`) — on Redis instead of `database`.
- [ ] **PostGIS** — move delivery zones from JSON to PostGIS polygons for DB-level spatial queries.

---

## 🚀 Installation

### Requirements

- PHP 8.3+, Composer 2
- Node.js 20+, npm
- PostgreSQL 16+
- **RAM:** at least 2 GB + **4–6 GB swap** for `npm run build` (building 4785 modules on 0.9 GB RAM dies with `Killed`/exit 137). On a 1 GB production server swap is mandatory.
- (Optional) Mapbox access token — for the delivery zone map
- (Optional) Yandex Geocoder key — for address suggestions
- (Optional) VK / Google OAuth keys
- (Optional) Telegram bot token + chat_id — for notifications
- (Optional) Yandex Static API key — for the map screenshot in Telegram
- (Optional) Mailtrap or `MAIL_MAILER=log` for local emails

### Setup

````bash
# 1. Clone and install dependencies
git clone https://github.com/Alexey96may/Rancho-Shop.git
cd Rancho-Shop

composer install
npm install

cp .env.example .env
php artisan key:generate

# 2. Edit .env (see "Environment variables")

# 3. Migrations and seeds
php artisan migrate --seed

# 4. Run everything with one command
composer dev

`composer dev` starts `php artisan serve`, `npm run dev`, the queue worker, and pail (logs).

### Demo Access

Created by `UserSeeder` (skipped in production). Default passwords:

| Role          | Phone                | Password    |
| ------------- | -------------------- | ----------- |
| **Admin**     | `+7 (999) 111-11-11` | `admin`     |
| **Moderator** | `+7 (999) 333-33-33` | `moderator` |
| **Worker**    | `+7 (999) 444-44-44` | `worker`    |
| **Customer**  | `+7 (999) 222-22-22` | `customer`  |

For the admin, you can override via .env: ADMIN_EMAIL, ADMIN_PHONE, ADMIN_PASSWORD.

### Environment Variables

The full list is in `.env.example`. Key ones:

```env
APP_URL=http://localhost:8000
APP_TIMEZONE_DISPLAY=Europe/Moscow

# Admin (overrides the hardcoded values in UserSeeder)
ADMIN_EMAIL=admin@rancho.com
ADMIN_PHONE=+7 (999) 111-11-11
ADMIN_PASSWORD=admin

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.yandex.ru
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rancho_db
DB_USERNAME=postgres
DB_PASSWORD=

# Frontend
VITE_MAPBOX_TOKEN=
VITE_YANDEX_GEOCODER_KEY=

# Yandex Static API (map screenshot in the Telegram bot)
YANDEX_STATIC_API_KEY=

# Payments
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

# Yandex Metrica
YANDEX_METRIKA_ID=

# AI-консультант (GigaChat)
GIGACHAT_AUTH_KEY=
GIGACHAT_SCOPE=GIGACHAT_API_PERS
GIGACHAT_DEFAULT_MODEL=GigaChat-2

# Honeypot (защита AI-чата от ботов)
HONEYPOT_ENABLED=true
HONEYPOT_NAME=my_name
HONEYPOT_VALID_FROM=valid_from
HONEYPOT_SECONDS=1
````

### Payment Drivers

`PAYMENT_DRIVER` accepts:

| Value       | Description                                                | Required variables                                              |
| ----------- | ---------------------------------------------------------- | --------------------------------------------------------------- |
| `fake`      | Instant payment simulation (recommended for local dev)     | —                                                               |
| `direct`    | Manual transfer via SBP / card — details shown to the user | `PAYMENT_DIRECT_*`                                              |
| `tinkoff`   | Tinkoff acquiring                                          | `TINKOFF_TERMINAL_KEY`, `TINKOFF_SECRET_KEY`, `TINKOFF_API_URL` |
| `yookassa`  | YooKassa                                                   | `YOOKASSA_SHOP_ID`, `YOOKASSA_SECRET_KEY`, `YOOKASSA_API_URL`   |
| `paymaster` | PayMaster                                                  | `PAYMASTER_MERCHANT_ID`, `PAYMASTER_SECRET_KEY`                 |

For local development use `fake` — real keys are not needed.

---

## 🖥 SSR (Inertia server-side rendering)

Disabled by default (`INERTIA_SSR_ENABLED=false`). To enable:

```bash
# 1. Build the SSR bundle
npm run build:ssr

# 2. Start the SSR server
php artisan inertia:start-ssr
```

Health check:

```bash
curl http://127.0.0.1:13714/health
# {"status":"OK","timestamp":...}
```

In `.env`:

```env
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714
```

In production, SSR runs via systemd (`milkshop-ssr.service`). During deployment, `npm run build:ssr` rebuilds `bootstrap/ssr`, then the service restarts, and `deploy.sh` waits for the `/health` response (retry for up to 30 seconds — SSR starts in ~9 seconds).

---

## 🤖 Telegram Bot

Notifications about new users and orders to a group chat, with a Yandex map screenshot (delivery address).

1. Create a bot via [@BotFather](https://t.me/BotFather), get `TELEGRAM_BOT_TOKEN`.
2. Add the bot to the group and grant it admin rights.
3. Get `TELEGRAM_ADMIN_CHAT_ID`:
    - via [@getidsbot](https://t.me/getidsbot), or
    - `https://api.telegram.org/bot<TOKEN>/getUpdates` (after a message in the group).
4. Get `YANDEX_STATIC_API_KEY` — a [Yandex Static API](https://yandex.ru/dev/maps/staticapi/) key for the map screenshot.
5. Set in `.env`:

```env
TELEGRAM_BOT_TOKEN=123456:ABC...
TELEGRAM_ADMIN_CHAT_ID=-1001234567890
YANDEX_STATIC_API_KEY=...
```

---

## 📊 Yandex Metrica

1. Create a counter on [metrika.yandex.ru](https://metrika.yandex.ru/) for `milkvalley.org`.
2. In `.env`:

```env
YANDEX_METRIKA_ID=12345678
```

3. `php artisan config:cache`.
4. The code is inserted in `resources/views/app.blade.php` via `config('services.yandex_metrika.id')`. Only `config(...)` goes into Git, without the real ID.

For Inertia SPA navigation, the mode without `ssr:true` is used — Metrica catches `pushState` itself. Goals (`reachGoal`) are sent via `ym(window.ymConfig.id, 'reachGoal', 'purchase')` after a successful order.

---

## 🖼 Images (Spatie Media Library)

- Collections: `avatars` (single), `gallery`, `voice` (single).
- Conversions: `thumb` (300×300), `preview` (1200×1200 max), plus `_webp` and `_avif` for both.
- Quality: `quality(75)` for webp, `quality(60)` for avif, `optimize()` for jpg.
- Frontend: `AppImage.vue` renders `<picture>` with `<source type="image/avif">` and `<source type="image/webp">`, fallback — `previews.original` (preview.jpg, not the original).
- Blur-up: `thumb_avif` (5 KB) as the blurred placeholder.

Regenerate conversions:

```bash
php artisan media-library:regenerate --ids=6,11 --force
```

`--ids=` accepts **`media.id`** (the primary key of the `media` table), not the file name.

### Fonts

Located in `public/fonts`. File names are **lowercase** (`spectral-bold.ttf`, `nunito-regular.ttf`), because Linux is case-sensitive and the CSS paths are `/fonts/spectral-bold.ttf`. If you rename them, update the CSS too (`resources/css/app.css`).

In CSS, specify only the formats that actually exist (`woff2`/`woff`/`ttf`), otherwise Vite will warn `didn't resolve at build time`.

---

## 🚢 Deploying to a VPS

Deployment via GitHub Actions + `deploy.sh` on the server.

### 1. SSH key for Actions

On the server:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy-milkshop" -f ~/.ssh/gh_actions_deploy -N ""
cat ~/.ssh/gh_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 2. GitHub Secrets

Repository → **Settings** → **Secrets and variables** → **Actions**:

| Name              | Value                                             |
| ----------------- | ------------------------------------------------- |
| `SSH_HOST`        | Server IP or domain                               |
| `SSH_USER`        | `root` (or a deploy user)                         |
| `SSH_PORT`        | `22`                                              |
| `SSH_PRIVATE_KEY` | the entire contents of `~/.ssh/gh_actions_deploy` |

### 3. `deploy.sh` on the server

Runs `git pull`, `composer install`, `npm ci` + `build` + `build:ssr` (if the frontend changed), `artisan` caches, migrations, queue/SSR restart, permissions on `storage`/`bootstrap/cache`.

Details:

- `NODE_OPTIONS="--max-old-space-size=2048"` for the build.
- Before the build — `sync` + `drop_caches` (frees page cache).
- SSR restarts and waits for `/health` with retry for up to 30 seconds (SSR starts in ~9 seconds).
- With `FRONTEND_CHANGED=true` (detected by `dorny/paths-filter` in the workflow) the frontend and SSR are built; otherwise skipped.

### 4. Running

GitHub → **Actions** → **Deploy to VSD** → **Run workflow**.

### Server Requirements

- **RAM:** at least 2 GB + **4–6 GB swap**. On 0.9 GB RAM, building 4785 modules fails with `Killed` (exit 137). Swap is mandatory.
- PHP 8.3, Composer, Node.js 20+, PostgreSQL 16.
- Nginx with `client_max_body_size 50M` (for photo uploads).
- PHP-FPM: `upload_max_filesize = 50M`, `post_max_size = 52M`.

Creating swap:

```bash
sudo fallocate -l 4G /swapfile2
sudo chmod 600 /swapfile2
sudo mkswap /swapfile2
sudo swapon /swapfile2
echo '/swapfile2 none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## Author

**Alexey** — Fullstack Developer
Laravel · Vue.js · TypeScript · PostgreSQL
