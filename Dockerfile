# syntax=docker/dockerfile:1.6

# ─────────────────────────────────────────────────────────────────────────────
# Klinik Aishi Beauty — Laravel 12 + Filament 4
# Target: Railway (atau platform Docker lainnya)
#
# Multi-stage:
#   1. deps      — composer + npm build (semua dependencies + assets)
#   2. runtime   — PHP 8.3 + Apache, ekstensi lengkap untuk Filament
# ─────────────────────────────────────────────────────────────────────────────

# =============================================================================
# Stage 1: build dependencies & frontend assets
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

# Install composer deps dulu (cached layer)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-progress \
        --prefer-dist \
        --no-autoloader

# Install npm deps
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund

# Bring in sisa source + build
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && npm run build


# =============================================================================
# Stage 2: runtime — PHP 8.3 + Apache
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

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy vhost template — $PORT akan di-substitute oleh entrypoint
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf.tmpl
RUN rm -f /etc/apache2/sites-enabled/000-default.conf && \
    ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www/html

# Bring in app + vendor + built assets
COPY --chown=www-data:www-data --from=deps /app ./

# Writable dirs
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
