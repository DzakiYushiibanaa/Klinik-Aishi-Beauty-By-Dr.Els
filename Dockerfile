# syntax=docker/dockerfile:1.6

# ─────────────────────────────────────────────────────────────────────────────
# Klinik Aishi Beauty — Laravel 12 + Filament 4
# Target: Railway (atau platform Docker lainnya)
#
# Multi-stage build:
#   1. deps      — composer deps + npm build
#   2. runtime   — PHP 8.3 + Apache dengan semua ekstensi yang dibutuhkan
# ─────────────────────────────────────────────────────────────────────────────

# =============================================================================
# Stage 1: composer + node (build dependencies & frontend assets)
# =============================================================================
FROM php:8.3-cli AS deps

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
        sockets \
        exif \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install PHP deps (cached layer saat composer.lock tidak berubah)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-progress \
        --prefer-dist \
        --no-autoloader

# Install & build frontend
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund

# Bring in the rest + finalize
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && npm run build


# =============================================================================
# Stage 2: runtime — PHP 8.3 + Apache
# =============================================================================
FROM php:8.3-apache

# Install ekstensi PHP yang dibutuhkan untuk Filament, PhpSpreadsheet, DomPDF, dll.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        gettext-base \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mysqli \
        gd \
        intl \
        zip \
        bcmath \
        sockets \
        exif \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Production PHP config
RUN { \
        echo 'memory_limit = 512M'; \
        echo 'upload_max_filesize = 20M'; \
        echo 'post_max_size = 20M'; \
        echo 'max_execution_time = 120'; \
        echo 'opcache.enable = 1'; \
        echo 'opcache.memory_consumption = 192'; \
        echo 'opcache.max_accelerated_files = 20000'; \
        echo 'opcache.validate_timestamps = 0'; \
    } > /usr/local/etc/php/conf.d/app.ini

# Apache: enable rewrite, docroot ke /public, listen di $PORT.
RUN a2enmod rewrite headers

# Vhost custom — sengaja inline biar nggak perlu copy file tambahan.
RUN rm -f /etc/apache2/sites-enabled/*.conf && \
    echo '<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    SetEnvIfNoCase X-Forwarded-Proto "https" HTTPS=on\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf && \
    a2ensite 000-default

WORKDIR /var/www/html

# Copy app + vendor + built assets dari stage deps
COPY --chown=www-data:www-data --from=deps /app ./

# Persiapkan folder writable
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint — substitusi $PORT + migrate + cache warm
COPY --chmod=755 <<'EOF' /usr/local/bin/entrypoint.sh
#!/bin/bash
set -e

cd /var/www/html

# Railway set PORT; fallback 8080 lokal
export PORT="${PORT:-8080}"

# Substitusi PORT di Apache config
sed -i "s/\${PORT}/${PORT}/g" /etc/apache2/sites-available/000-default.conf
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Generate APP_KEY kalau kosong
if [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force --no-interaction || true
fi

# Map Railway MySQL plugin vars → Laravel DB_* kalau user belum override
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-${MYSQL_DATABASE:-}}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-${MYSQL_ROOT_PASSWORD:-}}}"

# Cache warm-up (non-fatal kalau gagal)
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true
php artisan storage:link --force || true

# Migrate (idempotent)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || echo "[entrypoint] migrate gagal; cek env DB"
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction || true
fi

echo "[entrypoint] Apache listen di :${PORT}"
exec "$@"
EOF

ENV PORT=8080

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
