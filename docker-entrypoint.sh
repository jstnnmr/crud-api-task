#!/bin/sh
set -e

# Clear and re-cache config for production
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations if DB is reachable
if [ -n "$DB_HOST" ]; then
    echo "Running migrations..."
    php artisan migrate --force
else
    echo "DB_HOST not set, skipping migrations"
fi

# Link storage
php artisan storage:link || true

exec /usr/bin/supervisord -c /etc/supervisord.conf
