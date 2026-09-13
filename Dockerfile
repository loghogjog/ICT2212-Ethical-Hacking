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

# Copy source files and set ownership to www-data
COPY --chown=www-data:www-data src/ /var/www/html/
