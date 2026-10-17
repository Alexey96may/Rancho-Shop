#!/bin/bash
set -e

echo "🚀 Деплой начат..."

# 1. Git
echo "📥 Git pull..."
git pull origin main

# 2. Composer
echo "📦 Composer..."
composer install --no-dev --optimize-autoloader

# 3. NPM — только если менял фронт
# Раскомментируй, если нужно:
# echo "📦 NPM..."
# npm ci
# echo "🏗️  Build..."
# NODE_OPTIONS="--max-old-space-size=4096" npm run build

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

# 7. Права
echo "🔒 Права..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Деплой завершён!"
