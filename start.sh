#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

echo "Starting server..."
exec bash /assets/scripts/prestart.sh
