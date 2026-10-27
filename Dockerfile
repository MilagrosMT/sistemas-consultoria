# Etapa base: PHP, dependencias de Laravel y codigo de la aplicacion
FROM php:8.4-fpm AS php-deps

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

RUN mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/app/backups \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache


# Etapa frontend: Vite necesita las dependencias de Flux instaladas por Composer
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

COPY --from=php-deps /var/www/html/vendor ./vendor

RUN npm run build


# Etapa Nginx: recursos publicos compilados desde el mismo proyecto
FROM nginx:alpine AS nginx

COPY --from=frontend /app/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf


# Etapa final de Laravel con PHP-FPM
FROM php-deps AS app

COPY --from=frontend /app/public/build ./public/build

EXPOSE 9000

CMD ["php-fpm"]
