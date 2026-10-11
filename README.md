# Rancho — E-commerce for a Dairy Farm

Pet project of an online store for a small dairy farm: products tied to specific cows, delivery zones with PostGIS, and a multi-role admin panel.
Built with Laravel 13 + Vue 3 + Inertia.js.

---

## 📸 Demo

![Demo](docs/screenshots/farmshop-demo.gif)

| Home                                      | Shop                                          | Product                                         |
| ----------------------------------------- | --------------------------------------------- | ----------------------------------------------- |
| ![home](docs/screenshots/Rancho_main.jpg) | ![shop](docs/screenshots/Rancho_products.jpg) | ![animals](docs/screenshots/Rancho_animals.jpg) |

| Admin                                       | User Profile                              |
| ------------------------------------------- | ----------------------------------------- |
| ![admin](docs/screenshots/Rancho_admin.jpg) | ![user](docs/screenshots/Rancho_user.jpg) |

> No live demo yet — the project runs locally in a few minutes (see [Installation](#-installation)).

---

## 🛠 Tech Stack

**Backend**

- PHP 8.3, Laravel 13
- PostgreSQL + **PostGIS** (delivery zones)
- Laravel Sanctum (API auth for mobile client)
- Laravel Socialite (VK / Google login)
- Spatie Laravel Media Library
- HTMLPurifier (sanitizing rich-text content)
- Tightenco Ziggy (routes in JS)

**Architecture**

- Controllers · Services · **Actions · DTO · Enums · Exceptions · Helpers**
- Observers · Policies · Providers · **API Resources**
- Multi-role: `admin`, `moderator`, `worker`, `customer`
- Soft deletes for cows, products, reviews, pages

**Frontend**

- Vue 3 (Composition API) + TypeScript
- Inertia.js 2
- Vite 8
- Tailwind CSS + Typography plugin
- **Pinia** + persisted state (cart and settings in localStorage)
- **TipTap** — rich-text editor for admin content
- **ApexCharts** (`vue3-apexcharts`) — admin analytics
- **Mapbox GL + Turf.js** — delivery zone map and geo calculations
- **v-calendar** + date-fns — date pickers
- Swiper · Howler · VueUse · vuedraggable
- Headless UI · Heroicons · Lucide · Popper
- browser-image-compression — client-side image compression
- Lodash, lodash.debounce

**Quality**

- PHPUnit 12 (Unit + Feature)
- Laravel Pint (PSR-12)
- Laravel Pail (dev logs)
- Vitest + @vue/test-utils + jsdom
- ESLint 9 (Vue + TS) + Prettier (Tailwind & import-sort)
- barryvdh/laravel-ide-helper

---

## ✨ Features

### Storefront

- Landing with configurable blocks (hero, banners, FAQ, featured products)
- Product catalog with filters and pagination
- Product pages tied to a specific cow (or generic, cow-less)
- Cows section — each cow has a profile, bio, audio, gallery, genealogy
- Cart stored in Pinia (localStorage), prices re-synced from the server
- Checkout with promo codes and discounts
- Reviews (planned: social login via VK / Google)
- Static pages managed from the DB (About, Contacts, Terms, 404)
- Contact page with a Mapbox map

### Delivery

- Delivery zones stored as PostGIS polygons
- Outside a zone → no delivery; near a zone → surcharge
- Address picking with Mapbox + Turf.js distance checks
- **Smart route-based delivery**: admin defines a delivery route, user picks a point within the corridor radius

### Admin panel

- Products, cows, categories, orders, reviews, pages, settings
- Multi-role access: `admin`, `moderator`, `worker`
- TipTap rich-text editor for content
- ApexCharts dashboard
- Image galleries with drag & drop upload (Spatie Media Library)
- **Units with `low_stock_threshold`** — visual alert in admin when a product is running low

### SEO

- Morph SEO table with manual input per model
- Auto-generated OG / Twitter meta
- `robots.txt` editable from admin
- `sitemap.xml` generated with per-URL priorities and `is_noindex` filtering
- Canonical URLs
- Full ARIA coverage (roles, `aria-label`, `aria-live`, `aria-busy`, …)

### Integrations

- Laravel Socialite (VK / Google) — in progress
- API for an Android client (Sanctum) — partially implemented
- **Payments:** tested with YooKassa; also supports Direct / Tinkoff / PayMaster / Fake drivers via `PaymentGatewayInterface`

---

## ✅ Implemented

- [x] Clean architecture: Controllers, Services, Actions, DTO, Enums, Exceptions, Helpers, Observers, Policies, Providers, Resources
- [x] Roles: `admin`, `customer`, `moderator`, `worker`
- [x] Cart in UI with server-side validation via API
- [x] Smart delivery system: admin defines the route, user picks a point within the corridor radius
- [x] Units with `low_stock_threshold` — admin alert when a product is running low
- [x] Smart SEO: morph SEO table, manual input, `robots.txt` from admin, `sitemap.xml`
- [x] Soft deletes across cows, products, reviews, pages
- [x] Role-based access control
- [x] Settings with caching (24h TTL, invalidated on save)
- [x] Discounts & promo codes (DB + admin only)
- [x] Payments: YooKassa (tested), Direct, Tinkoff, Fake drivers via unified `PaymentGatewayInterface`

## 🔮 Roadmap

- [ ] Tie "Buy" / "Pre-order" buttons to the delivery schedule
- [ ] SSR (Inertia server-side rendering)
- [ ] Promo codes in payment flow and public UI
- [ ] Skeleton loaders for cards

Full list → [docs/ROADMAP.md](docs/ROADMAP.md)

---

## 🚀 Installation

### Requirements

- PHP 8.3+, Composer 2
- Node.js 20+, npm
- PostgreSQL 16+ **with PostGIS extension**
- (Optional) Mapbox access token — for the delivery-zone map
- (Optional) Yandex Geocoder key — for address suggestions
- (Optional) VK / Google OAuth credentials
- (Optional) Mailtrap or `MAIL_MAILER=log` for local email

### 1. Clone and install

### Steps

```bash
https://github.com/Alexey96may/Rancho-Shop.git
cd fRancho-Shop

composer install
npm install

cp .env.example .env
php artisan key:generate

# 2. One-command setup
# Installs PHP + JS deps, copies .env, generates key, migrates, builds assets
composer setup


# 3. Edit .env:
#   APP_URL=http://localhost:8000

#   DB_CONNECTION=pgsql
#   DB_HOST=127.0.0.1
#   DB_PORT=5432
#   DB_DATABASE=rancho_db
#   DB_USERNAME=postgres
#   DB_PASSWORD=your_password

#   VITE_MAPBOX_TOKEN=             # delivery zone map
#   VITE_YANDEX_GEOCODER_KEY=      # address suggestions

#   PAYMENT_DRIVER=fake            # "fake" | "direct" | "tinkoff" | "yookassa" | "paymaster"

# 4. Enable PostGIS in the database
psql -U postgres -d rancho_db -c "CREATE EXTENSION IF NOT EXISTS postgis;"

# 5. Migrate and seed
php artisan migrate --seed

# 6. Run everything with one command
composer dev
```

### Demo credentials

| Role          | Email                  | Password    |
| ------------- | ---------------------- | ----------- |
| **Admin**     | `admin@rancho.com`     | `admin`     |
| **Moderator** | `moderator@rancho.com` | `moderator` |
| **Worker**    | `worker@rancho.com`    | `worker`    |
| **Customer**  | `customer@rancho.com`  | `customer`  |

### Payment drivers

`PAYMENT_DRIVER` accepts:

| Value       | Description                                               | Required env vars                                               |
| ----------- | --------------------------------------------------------- | --------------------------------------------------------------- |
| `fake`      | Instant simulated payment (recommended for local dev)     | —                                                               |
| `direct`    | Manual SBP / card transfer — requisites shown to the user | `PAYMENT_DIRECT_*`                                              |
| `tinkoff`   | Tinkoff E-Acquiring                                       | `TINKOFF_TERMINAL_KEY`, `TINKOFF_SECRET_KEY`, `TINKOFF_API_URL` |
| `yookassa`  | YooKassa                                                  | `YOOKASSA_SHOP_ID`, `YOOKASSA_SECRET_KEY`, `YOOKASSA_API_URL`   |
| `paymaster` | PayMaster                                                 | `PAYMASTER_MERCHANT_ID`, `PAYMASTER_SECRET_KEY`                 |

For local development use `fake` — no real keys needed.
