# --- BUILD DEPENDENCIES --- #
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress


# --- PRODUCTION --- #
FROM php:8.2-apache

ARG USER_UID=1002
ARG USER_GID=1002
ARG USERNAME=web-admin

WORKDIR /var/www/html

# Install SQLite dependencies/extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
        openssl \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Create low-privilege application user
RUN groupadd --gid "$USER_GID" "$USERNAME" \
    && useradd --uid "$USER_UID" --gid "$USER_GID" -m "$USERNAME"

# SQLite database directory
RUN mkdir -p /var/www/db \
    && chown -R "$USERNAME:$USERNAME" /var/www/db

COPY --chown="$USERNAME:$USERNAME" \
    database/database.sqlite \
    /var/www/db/database.sqlite

# Apache modules
RUN a2enmod rewrite ssl headers

# Application
COPY --from=builder /app/vendor ./vendor
COPY --chown="$USERNAME:$USERNAME" src/ ./

# Upload directory
RUN mkdir -p /var/www/html/uploads \
    && chown "$USERNAME:$USERNAME" /var/www/html/uploads

RUN printf '<Directory /var/www/html/uploads>\n\
    AllowOverride All\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# --- SSL --- #
RUN mkdir -p /etc/apache2/ssl \
    && openssl req -x509 -nodes -days 365 \
        -newkey rsa:2048 \
        -keyout /etc/apache2/ssl/selfsigned.key \
        -out /etc/apache2/ssl/selfsigned.crt \
        -subj "/C=SG/ST=Singapore/L=Singapore/O=Helpdesk/CN=localhost" \
    && chown "$USERNAME:$USERNAME" /etc/apache2/ssl/selfsigned.key \
    && chmod 600 /etc/apache2/ssl/selfsigned.key \
    && chmod 644 /etc/apache2/ssl/selfsigned.crt

# Apache listens on unprivileged HTTPS port
RUN sed -i 's/Listen 443/Listen 8443/' /etc/apache2/ports.conf

COPY ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN chmod 644 /etc/apache2/sites-available/default-ssl.conf

RUN a2ensite default-ssl

# Security headers / banner reduction
RUN printf 'Header always set X-Frame-Options "DENY"\n\
Header always set X-Content-Type-Options "nosniff"\n\
Header always set Referrer-Policy "no-referrer-when-downgrade"\n' \
    > /etc/apache2/conf-available/security-headers.conf \
    && a2enconf security-headers \
    && printf '\nServerTokens Prod\nServerSignature Off\n' \
    >> /etc/apache2/apache2.conf

# Challenge flag
COPY --chown="$USERNAME:$USERNAME" entry.flag /home/$USERNAME/entry.flag
RUN chmod 444 /home/$USERNAME/entry.flag

USER $USERNAME
