FROM php:8.3.9RC1-fpm-alpine

LABEL author="Yasfa Ainun Abdullah"

WORKDIR /var/www/app

RUN apk update && apk add \
    nano \
    unzip

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/app

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Jalankan composer install
RUN composer install

RUN php artisan storage:link

# Ubah hak akses direktori (opsional)
RUN chown -R www-data:www-data /var/www/app/storage

USER www-data