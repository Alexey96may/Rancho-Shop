#!/bin/bash
set -e

echo "🚀 Деплой начат..."

# 1. Git
echo "📥 Git pull..."
git pull origin main

# 2. Composer
echo "📦 Composer..."
composer install --no-dev --optimize-autoloader

# 3. NPM / Build / SSR
# Раскомментируй, если в этом деплое менялся фронт или VITE_* переменные:
# echo "📦 NPM..."
# npm ci
#
# echo "🏗️  Build (client)..."
# NODE_OPTIONS="--max-old-space-size=4096" npm run build
#
# echo "🏗️  Build (ssr)..."
# rm -rf bootstrap/ssr
# NODE_OPTIONS="--max-old-space-size=4096" npm run build:ssr

# 4. Кэш
echo "🧹 Очистка..."
php artisan optimize:clear

echo "📦 Кэширование..."
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# 5. Миграции
echo "🗄️  Миграции..."
php artisan migrate --force

# 6. Очередь
echo "🔄 Очередь..."
sudo systemctl restart milkshop-queue

# 7. SSR — перезапуск ТОЛЬКО если собирал фронт (см. блок 3)
echo "🖥️  SSR..."
sudo systemctl restart milkshop-ssr
sleep 2
curl -fsS http://127.0.0.1:13714/health >/dev/null && echo "   ✓ SSR отвечает" || {
    echo "   ✗ SSR не отвечает, смотри: journalctl -u milkshop-ssr -n 50"
    exit 1
}

# 8. Права
echo "🔒 Права..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Деплой завершён!"
