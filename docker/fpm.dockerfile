FROM php:7.3-fpm

RUN  apt-get update \
    && apt-get install -y \
         libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

WORKDIR /var/www/bit24