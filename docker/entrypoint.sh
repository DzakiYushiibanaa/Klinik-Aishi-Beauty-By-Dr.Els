#!/usr/bin/env bash
set -e

PORT="${PORT:-10000}"

echo "[entrypoint] Rendering Apache vhost for port :${PORT}"

if [ -f /etc/apache2/sites-available/000-default.conf.tmpl ]; then
    envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf.tmpl > /etc/apache2/sites-available/000-default.conf
fi

sed -i "s/Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan migrate --force || true

echo "[entrypoint] Apache starting on :${PORT}"

exec "$@"