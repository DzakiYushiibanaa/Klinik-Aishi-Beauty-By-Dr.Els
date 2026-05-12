#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-10000}"
export PORT

echo "[entrypoint] Preparing Apache MPM"

# Fix Apache MPM:
# Apache must load exactly one MPM.
# Remove all active MPM symlinks, then enable only mpm_prefork.
rm -f /etc/apache2/mods-enabled/mpm_*.load || true
rm -f /etc/apache2/mods-enabled/mpm_*.conf || true

ln -sf ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load

if [ -f /etc/apache2/mods-available/mpm_prefork.conf ]; then
    ln -sf ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
fi

echo "[entrypoint] Rendering Apache vhost for port :${PORT}"

if [ -f /etc/apache2/sites-available/000-default.conf.tmpl ]; then
    envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf.tmpl > /etc/apache2/sites-available/000-default.conf
fi

# Force Apache listen to Railway dynamic PORT
echo "Listen ${PORT}" > /etc/apache2/ports.conf

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan migrate --force || true

echo "[entrypoint] Apache config test"
apache2ctl configtest

echo "[entrypoint] Apache starting on :${PORT}"

exec "$@"