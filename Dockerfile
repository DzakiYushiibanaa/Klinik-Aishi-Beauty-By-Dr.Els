# syntax=docker/dockerfile:1.6

# ─────────────────────────────────────────────────────────────────────────────
# Klinik Aishi Beauty — Laravel 12 + Filament 4
# Target: Railway / Docker platforms
#
# Multi-stage:
#   1. deps      — install composer deps + build Vite assets
#   2. runtime   — PHP 8.3 + Apache production runtime
# ─────────────────────────────────────────────────────────────────────────────


# =============================================================================
# Stage 1 — Dependencies + Frontend Build
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
    && curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy composer manifest
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-progress \
        --prefer-dist \
        --no-autoloader

# Copy npm manifest
COPY package.json package-lock.json* ./

# Install node modules
RUN npm ci --no-audit --no-fund

# Copy application source
COPY . .

# Generate optimized autoload
RUN composer dump-autoload \
        --optimize \
        --no-dev \
        --classmap-authoritative

# Build Vite assets
RUN npm run build


# =============================================================================
# Stage 2 — Runtime
# =============================================================================
FROM php:8.3-apache

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

# PHP production config
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

# Apache modules
# php:8.3-apache already uses mpm_prefork by default.
# Only disable conflicting MPM modules.
RUN find /etc/apache2 -name "mpm_*.load" -delete \
    && find /etc/apache2 -name "mpm_*.conf" -delete \
    && a2enmod rewrite headers

# Optional composer in runtime
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache vhost template
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf.tmpl

WORKDIR /var/www/html

# Copy app from build stage
COPY --chown=www-data:www-data --from=deps /app ./

# Writable directories
RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Railway dynamic port fallback
ENV PORT=10000

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["apache2-foreground"]