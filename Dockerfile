FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (for caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy project files
COPY . .

# Run composer scripts
RUN composer run-script post-autoload-dump --no-interaction || true

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start command
CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
