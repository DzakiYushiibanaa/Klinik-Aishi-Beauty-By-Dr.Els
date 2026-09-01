FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    supervisor

# Clear apt cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl sockets xml

# Install Node.js & npm (for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set permissions for the working directory
RUN chown -R www-data:www-data /var/www/html

# Copy the rest of the application
COPY . /var/www/html

# Switch to non-root user (www-data) if needed, but for simplicity of permissions in local dev we often stick to root or a defined user.
# Here we'll just allow the entrypoint script or command to handle it.

# Expose Octane port and Vite port
EXPOSE 8042
EXPOSE 5173

# Start the application using RoadRunner Octane
# In a real environment, you'd want a startup script, but this works for development
CMD bash -c "composer install --no-interaction && npm install && npm run build && php artisan octane:start --server=roadrunner --host=0.0.0.0 --port=8042"