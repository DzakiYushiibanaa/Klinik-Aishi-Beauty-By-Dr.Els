# syntax=docker/dockerfile:1.6

# ─────────────────────────────────────────────────────────────────────────────
# Klinik Aishi Beauty — Laravel 12 + Filament 4 production image
#
# Target: Render.com (or any Docker host). Uses a multi-stage build:
#   1. composer  — install PHP dependencies (no dev)
#   2. assets    — build front-end bundle with Vite + Tailwind
#   3. runtime   — PHP 8.3 + Apache (mod_rewrite) with required extensions
#
# The container listens on $PORT (Render injects it, defaults to 10000 locally).
# ─────────────────────────────────────────────────────────────────────────────

# =============================================================================
# 1) Composer stage — install PHP deps
# =============================================================================
FROM composer:2.7 AS composer

WORKDIR /app

# Copy only the files needed for `composer install` first so Docker can cache
# this layer when the dependency manifest hasn't changed.
COPY composer.json composer.lock ./

# The `post-autoload-dump` script calls artisan which needs the full app to
# exist. Skip scripts & autoloader during install, then dump autoload later.
#
# The composer:2.7 image is Alpine-based and doesn't ship php-intl / php-gd.
# We don't need them in *this* stage — platform checks will succeed at runtime
# where the full PHP extension set is installed. So ignore platform reqs.
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-progress \
        --prefer-dist \
        --no-autoloader \
        --ignore-platform-reqs

# Now bring in the rest of the app and generate the optimized autoload.
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative


# =============================================================================
# 2) Assets stage — build Vite bundle
# =============================================================================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund

COPY . .
# Vite needs the composer-installed vendor tree so tailwind/filament plugins
# resolve their views/blade templates for content scanning.
COPY --from=composer /app/vendor ./vendor
RUN npm run build


# =============================================================================
# 3) Runtime stage — PHP 8.3 + Apache
# =============================================================================
FROM php:8.3-apache

# Install system packages + PHP extensions required by the project:
# - pdo_mysql, pdo_pgsql : database drivers (Render typically provides Postgres)
# - gd                   : DomPDF, Excel, image handling
# - intl                 : Carbon translated formats, Filament locale features
# - zip                  : phpspreadsheet (maatwebsite/excel)
# - bcmath               : financial calculations
# - sockets              : mike42/escpos-php network printing
# - opcache              : bytecode cache for prod
# - exif, fileinfo       : file uploads
# - redis (via pecl)     : optional queue/cache driver
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
        libpq-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gd \
        intl \
        zip \
        bcmath \
        sockets \
        exif \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
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
        echo 'opcache.interned_strings_buffer = 16'; \
        echo 'opcache.max_accelerated_files = 20000'; \
        echo 'opcache.validate_timestamps = 0'; \
        echo 'opcache.preload_user = www-data'; \
    } > /usr/local/etc/php/conf.d/app.ini

# Copy composer binary so `artisan` and deploy scripts can invoke it if needed
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Enable mod_rewrite & mod_headers for Laravel's public/.htaccess to work.
RUN a2enmod rewrite headers

# Custom Apache vhost that points to /public and listens on ${PORT}.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/ports.conf /etc/apache2/ports.conf

WORKDIR /var/www/html

# Bring in the application with vendor deps and built assets.
COPY --chown=www-data:www-data --from=composer /app ./
COPY --chown=www-data:www-data --from=assets   /app/public/build ./public/build

# Ensure runtime-writable dirs exist with correct perms.
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint — applies Apache port substitution, runs migrations, caches config.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Render sets PORT dynamically; fall back to 10000 for local `docker run`.
ENV PORT=10000

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
