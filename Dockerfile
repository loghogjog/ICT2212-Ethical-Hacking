FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
 && docker-php-ext-install pdo pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*

COPY src/ /var/www/html

RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html
