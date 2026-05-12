#!/bin/bash

set -e

cd /var/www/html

export PORT="${PORT:-10000}"

envsubst '${PORT}' \
    < /etc/apache2/sites-available/000-default.conf.tmpl \
    > /etc/apache2/sites-available/000-default.conf

echo "Listen ${PORT}" > /etc/apache2/ports.conf

export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"

mkdir -p storage/framework/{sessions,views,cache,testing} \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

php artisan storage:link --force || true

php artisan optimize:clear || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || true
fi

echo "[entrypoint] Apache starting on :${PORT}"

exec "$@"