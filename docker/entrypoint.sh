#!/usr/bin/env bash
#
# Container entrypoint untuk Laravel + Apache.
#
# Responsibilities:
#   1. Substitute ${PORT} ke Apache config (Railway inject port dinamis).
#   2. Pastikan APP_KEY ada (generate kalau kosong).
#   3. Warm Laravel cache (config/route/view) untuk performance production.
#   4. Run database migrations saat boot (idempotent, pakai --force).
#   5. Hand off ke command asli (apache2-foreground by default).
#
# Non-zero exit di sini akan membunuh container dan Railway akan restart.
# ──────────────────────────────────────────────────────────────────────────

set -euo pipefail

cd /var/www/html

# 1. Railway set PORT; fallback 10000 untuk `docker run` lokal.
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/apache2/ports.conf > /etc/apache2/ports.conf.tmp \
    && mv /etc/apache2/ports.conf.tmp /etc/apache2/ports.conf
envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf > /etc/apache2/sites-available/000-default.conf.tmp \
    && mv /etc/apache2/sites-available/000-default.conf.tmp /etc/apache2/sites-available/000-default.conf

# 2. APP_KEY — generate kalau missing supaya brand-new Railway service boot clean.
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "" ]; then
    echo "[entrypoint] APP_KEY empty; generating temporary key (set APP_KEY di Railway Variables)."
    php artisan key:generate --force --no-interaction || true
fi

# 3. Railway MySQL plugin: kalau user set MYSQLHOST/MYSQLUSER dkk, mapping
#    otomatis ke DB_HOST/DB_USERNAME dkk supaya Laravel baca.
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"

# Keep permissions sane — Railway may mount a persistent volume on storage/.
mkdir -p storage/framework/{sessions,views,cache,testing} \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# 3. Production cache warm-up. Any failure here is logged but non-fatal so a
#    misconfigured env var doesn't crash-loop the container immediately.
php artisan config:cache     || echo "[entrypoint] config:cache failed"
php artisan route:cache      || echo "[entrypoint] route:cache failed"
php artisan view:cache       || echo "[entrypoint] view:cache failed"
php artisan event:cache      || echo "[entrypoint] event:cache failed"
php artisan filament:cache-components || true

# 4. Storage symlink (public/storage → storage/app/public). Re-create each boot
#    because Railway doesn't persist the symlink by default.
php artisan storage:link --force || true

# 5. DB migrations — idempotent thanks to migrations table.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || echo "[entrypoint] migrate failed; check DB env"
fi

# Optional: seed only if explicitly requested (don't want to overwrite data).
if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction || true
fi

echo "[entrypoint] Starting Apache on port ${PORT}"
exec "$@"
