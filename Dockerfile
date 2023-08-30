FROM php:8.1-fpm-alpine AS php
RUN apk add -U --no-cache curl-dev
RUN docker-php-ext-install curl
RUN docker-php-ext-install exif

RUN apk add autoconf g++ make
RUN pecl install apcu && docker-php-ext-enable apcu

RUN docker-php-ext-install pdo_mysql
RUN install -o www-data -g www-data -d /var/www/upload/image/

