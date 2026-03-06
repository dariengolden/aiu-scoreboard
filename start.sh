#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

echo "Starting server..."
# Generate nginx config from template, then start PHP-FPM and Nginx
# This replicates Nixpacks' default PHP start command
node /assets/scripts/prestart.mjs /assets/nginx.template.conf /nginx.conf
php-fpm -y /assets/php-fpm.conf &
exec nginx -c /nginx.conf
