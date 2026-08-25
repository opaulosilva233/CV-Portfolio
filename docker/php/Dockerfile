# Stage 1: Build frontend assets
FROM node:22-alpine AS node-builder

WORKDIR /app

# Copy dependency files first to leverage Docker layer caching
COPY package.json package-lock.json ./

RUN npm ci

# Copy source files required for building assets
COPY resources/ resources/
COPY public/ public/
COPY vite.config.js postcss.config.js tailwind.config.js jsconfig.json ./

RUN npm run build

# Stage 2: Build PHP dependencies
FROM composer:2 AS composer-builder

WORKDIR /app

# Copy composer files first to leverage Docker layer caching
COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY app/ app/
COPY database/ database/
COPY config/ config/
COPY routes/ routes/
COPY bootstrap/ bootstrap/

RUN composer dump-autoload --optimize --no-dev

# Stage 3: Production PHP runtime image
FROM php:8.3-fpm

# Install system dependencies and PHP extensions
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
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set www-data to match host user UID/GID to avoid permission issues
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy compiled dependencies and frontend build artifacts from previous stages
COPY --from=composer-builder /app/vendor /var/www/html/vendor
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Configure OPcache and PHP upload limits
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Ensure permissions
RUN chown -R www-data:www-data /var/www/html
