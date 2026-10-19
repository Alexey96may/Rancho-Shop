#!/bin/bash
set -e

echo "🚀 Deployment started..."

# 1. Git
echo "📥 Git pull..."
git pull origin main

# 2. Composer
echo "📦 Composer..."
composer install --no-dev --optimize-autoloader

# 3. NPM / Build / SSR — only if FRONTEND_CHANGED=true (set by CI)
if [ "${FRONTEND_CHANGED:-false}" = "true" ]; then
    echo "📦 NPM..."
    npm ci

    echo "🏗️  Build (client)..."
    NODE_OPTIONS="--max-old-space-size=4096" npm run build

    echo "🏗️  Build (ssr)..."
    rm -rf bootstrap/ssr
    NODE_OPTIONS="--max-old-space-size=4096" npm run build:ssr
else
    echo "⏭️  Skipping frontend build (no frontend changes)."
fi

# 4. Cache
echo "🧹 Clearing cache..."
php artisan optimize:clear

echo "📦 Caching..."
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# 5. Migrations
echo "🗄️  Migrations..."
php artisan migrate --force

# 6. Queue
echo "🔄 Queue..."
sudo systemctl restart milkshop-queue

# 7. SSR — restart ONLY if frontend was rebuilt
if [ "${FRONTEND_CHANGED:-false}" = "true" ]; then
    echo "🖥️  SSR..."
    sudo systemctl restart milkshop-ssr
    sleep 2
    curl -fsS http://127.0.0.1:13714/health >/dev/null && echo "   ✓ SSR is responding" || {
        echo "   ✗ SSR is not responding, check: journalctl -u milkshop-ssr -n 50"
        exit 1
    }
else
    echo "⏭️  Skipping SSR restart (no frontend changes)."
fi

# 8. Permissions
echo "🔒 Permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
