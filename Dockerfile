# --- Stage 1: Build PHP dependencies ---
FROM composer:2 AS composer_build

WORKDIR /app

# composer.lock がなくても動くようにワイルドカードを使用
COPY composer.json composer.loc[k] ./

# --ignore-platform-reqs を追加（PHPバージョンや拡張の厳密なチェックを無視）
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-reqs

# --- Stage 2: Production image ---
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer_build /app/vendor /var/www/html/vendor
COPY . /var/www/html

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini

EXPOSE 80

CMD ["apache2-foreground"]