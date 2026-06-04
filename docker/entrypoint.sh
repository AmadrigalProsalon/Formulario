#!/bin/sh
set -e

mkdir -p storage/logs
mkdir -p storage/app
mkdir -p storage/app/public
mkdir -p storage/app/templates
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

if [ -f resources/templates/formato_permiso.docx ] && [ ! -f storage/app/templates/formato_permiso.docx ]; then
    cp resources/templates/formato_permiso.docx storage/app/templates/formato_permiso.docx || true
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec "$@"
