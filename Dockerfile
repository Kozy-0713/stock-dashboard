# --- Stage 1: PHP & Node.js のビルド ---
FROM composer:2 AS build_stage
WORKDIR /app

# すべてのファイルをコピー（package.json や vite.config.js が必要）
COPY . .

# 1. PHPの依存関係インストール
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-reqs --no-scripts

# 2. Node.js をインストールして Vite でビルド
RUN apk add --no-cache nodejs npm \
    && npm install \
    && npm run build

# --- Stage 2: 本番用イメージ ---
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Stage 1 から vendor と public/build（Viteの成果物）をコピー
COPY --from=build_stage /app/vendor /var/www/html/vendor
COPY --from=build_stage /app/public/build /var/www/html/public/build
COPY . /var/www/html

# Apacheの設定
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 権限付与
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini

EXPOSE 80

CMD ["apache2-foreground"]