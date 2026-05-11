#!/usr/bin/env bash
#
# Container entrypoint for Laravel + Apache.
#
# Responsibilities:
#   1. Substitute ${PORT} into Apache configs (Render injects a random port).
#   2. Ensure APP_KEY exists (generated from env or on-the-fly fallback).
#   3. Warm Laravel caches (config/route/view) for production performance.
#   4. Run database migrations on boot (safe to re-run, uses --force).
#   5. Hand off to the original command (apache2-foreground by default).
#
# Any non-zero exit here will kill the container and Render will restart it.
# ──────────────────────────────────────────────────────────────────────────

set -euo pipefail

cd /var/www/html

# 1. Render sets PORT; fall back to 10000 for `docker run` locally.
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/apache2/ports.conf > /etc/apache2/ports.conf.tmp \
    && mv /etc/apache2/ports.conf.tmp /etc/apache2/ports.conf
envsubst '${PORT}' < /etc/apache2/sites-available/000-default.conf > /etc/apache2/sites-available/000-default.conf.tmp \
    && mv /etc/apache2/sites-available/000-default.conf.tmp /etc/apache2/sites-available/000-default.conf

# 2. APP_KEY — generate if missing so a brand-new Render service boots cleanly.
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "" ]; then
    echo "[entrypoint] APP_KEY empty; generating temporary key (set APP_KEY in Render dashboard)."
    php artisan key:generate --force --no-interaction || true
fi

# Keep permissions sane — Render may mount a persistent disk on storage/.
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
#    because Render doesn't persist the symlink by default.
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
