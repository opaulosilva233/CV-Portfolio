FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    procps \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set www-data to match host user UID/GID to avoid permission issues
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Set working directory
WORKDIR /var/www/html

# Ensure permissions (simplistic approach for dev)
RUN chown -R www-data:www-data /var/www/html
# Configure OPcache for better performance
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
# Configure PHP upload limits
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
