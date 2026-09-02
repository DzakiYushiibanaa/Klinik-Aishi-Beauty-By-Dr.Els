#!/usr/bin/env bash
set -euo pipefail

# Fallback port if not provided by Railway/Docker
PORT="${PORT:-8042}"
export PORT

echo "Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

echo "Linking storage..."
php artisan storage:link || true

echo "Optimizing Filament..."
php artisan filament:optimize-clear || true

echo "Waiting for database connection..."
# Loop until artisan command can successfully connect to the database
while ! php artisan db:show > /dev/null 2>&1; do
    echo "Database is not ready yet. Retrying in 3 seconds..."
    sleep 3
done
echo "Database connected successfully!"

echo "Running migrations and seeders..."
php artisan migrate --force --seed || true

echo "Starting Laravel Octane (RoadRunner) on port ${PORT}..."
exec php artisan octane:start --server=roadrunner --host=0.0.0.0 --port="${PORT}"
