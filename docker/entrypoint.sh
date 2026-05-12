#!/bin/bash
#
# Railway entrypoint untuk Laravel + Apache.
# Tasks: substitute $PORT, map Railway MySQL vars, migrate, warm cache.

set -e

cd /var/www/html

# Railway set PORT; fallback 8080 untuk docker run lokal.
export PORT="${PORT:-8080}"

# Substitusi $PORT di vhost + ports.conf (pakai envsubst biar reliable)
envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf.tmpl \
    > /etc/apache2/sites-available/000-default.conf
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Generate APP_KEY kalau kosong
if [ -z "${APP_KEY:-}" ]; then
    echo "[entrypoint] APP_KEY kosong, generate temporary..."
    php artisan key:generate --force --no-interaction || true
fi

# Map Railway MySQL plugin env vars → Laravel DB_* kalau user belum override
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-${MYSQL_DATABASE:-}}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-${MYSQL_ROOT_PASSWORD:-}}}"

# Siapkan folder writable (Railway volume bisa mount ulang)
mkdir -p storage/framework/{sessions,views,cache,testing} \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# Storage symlink
php artisan storage:link --force || true

# Warm cache (non-fatal)
php artisan config:cache || echo "[entrypoint] config:cache failed"
php artisan route:cache  || echo "[entrypoint] route:cache failed"
php artisan view:cache   || echo "[entrypoint] view:cache failed"

# Migrate (idempotent)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || echo "[entrypoint] migrate gagal; cek env DB"
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction || true
fi

echo "[entrypoint] Apache starting on :${PORT}"
exec "$@"
