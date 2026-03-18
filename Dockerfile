# --- Stage 1: Build PHP dependencies ---
FROM composer:2 AS composer_build

WORKDIR /app

COPY composer.json composer.lock ./

# Install production dependencies only to save time and memory
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# --- Stage 2: Production image ---
FROM php:8.3-apache

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module for Laravel routing
RUN a2enmod rewrite

# Copy the built dependencies from the first stage
COPY --from=composer_build /app/vendor /var/www/html/vendor

# Copy the rest of the application code
COPY . /var/www/html

# Configure Apache document root for Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set proper permissions for Laravel directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Set memory limit to avoid runtime memory errors in Free tier
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini

EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]