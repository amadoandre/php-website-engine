 FROM php:8.1.0alpha2-fpm-buster

# Install system dependencies
RUN apt-get update && apt-get install -y git libpq-dev

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www