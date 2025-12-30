FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    mariadb-client \
    make \
    zlib-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/app

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Create a user to match host UID/GID if needed (optional for local dev, but good practice)
# For simplicity in this environment, we'll stay as root or default
