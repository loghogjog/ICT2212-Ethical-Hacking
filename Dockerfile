FROM php:8.2-apache

# Install SQLite extensions & clean up
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Directory for SQLite database
RUN mkdir -p /var/www/db && chown -R www-data:www-data /var/www/db

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Apache config to listen on port 8080
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

# Copy source files and set ownership to www-data
COPY --chown=www-data:www-data src/ /var/www/html/

USER www-data

cmd ["apache2-foreground"]
