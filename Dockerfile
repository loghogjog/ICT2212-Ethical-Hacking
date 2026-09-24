FROM php:8.2-apache

# Build Args from compose.yaml
ARG USER_UID=1002
ARG USER_GID=1002
ARG USERNAME=web-admin

# Install SQLite extensions & clean up
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Create group and user using imported IDs
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME

# Directory for SQLite database
RUN mkdir -p /var/www/db && chown -R $USERNAME:$USERNAME /var/www/db && chmod 775 /var/www/db

##COPY --chown=$USERNAME:$USERNAME database/database.sqlite /var/www/db/database.sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Apache config to listen on port 8080
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

# Copy source files and set ownership to $USERNAME
COPY --chown=$USERNAME:$USERNAME src/ /var/www/html/

RUN mkdir -p /var/www/html/uploads && chown $USERNAME:$USERNAME /var/www/html/uploads

RUN printf '<Directory /var/www/html/uploads>\n    AllowOverride All\n</Directory>\n' >> /etc/apache2/apache2.conf

USER $USERNAME
