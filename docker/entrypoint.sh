#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-10000}"

echo "[entrypoint] Cleaning Apache MPM dynamic modules"

# Fix: prevent Apache error "More than one MPM loaded"
# Railway/php-apache can conflict if MPM is loaded dynamically more than once.
rm -f /etc/apache2/mods-enabled/mpm_*.load || true
rm -f /etc/apache2/mods-enabled/mpm_*.conf || true

echo "[entrypoint] Rendering Apache vhost for port :${PORT}"

if [ -f /etc/apache2/sites-available/000-default.conf.tmpl ]; then
    envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf.tmpl > /etc/apache2/sites-available/000-default.conf
fi

# Force Apache listen to Railway dynamic PORT
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Laravel runtime clear cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Database migration
php artisan migrate --force || true

echo "[entrypoint] Apache config test"
apache2ctl configtest

echo "[entrypoint] Apache starting on :${PORT}"

exec "$@"