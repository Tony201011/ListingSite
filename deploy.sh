#!/usr/bin/env bash
# Deployment script for Laravel application
# Run this script after deploying/pulling code to the server:
#   sudo bash deploy.sh
# Or as the www-data user:
#   bash deploy.sh

set -e

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
WEB_USER="${WEB_USER:-www-data}"

echo "==> Deploying from: $APP_DIR"

# Install/update PHP dependencies
echo "==> Installing Composer dependencies..."
composer install --no-interaction --no-dev --optimize-autoloader

# Install/update JS dependencies and build assets
echo "==> Installing Node dependencies and building assets..."
npm ci --ignore-scripts
npm run build

# Fix storage and bootstrap/cache directory permissions
echo "==> Fixing storage permissions..."
mkdir -p \
    "$APP_DIR/storage/app/private" \
    "$APP_DIR/storage/app/public" \
    "$APP_DIR/storage/framework/cache/data" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/testing" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache"

chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Clear and rebuild caches
echo "==> Clearing application caches..."
php "$APP_DIR/artisan" cache:clear
php "$APP_DIR/artisan" view:clear
php "$APP_DIR/artisan" config:clear
php "$APP_DIR/artisan" route:clear

echo "==> Caching configuration for production..."
php "$APP_DIR/artisan" config:cache
php "$APP_DIR/artisan" route:cache
php "$APP_DIR/artisan" view:cache

# Run database migrations
echo "==> Running database migrations..."
php "$APP_DIR/artisan" migrate --force

# Create storage symlink if needed
echo "==> Creating storage symlink..."
php "$APP_DIR/artisan" storage:link --force 2>/dev/null || true

echo "==> Deployment complete."
