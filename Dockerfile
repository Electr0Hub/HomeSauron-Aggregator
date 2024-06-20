# Use the official PHP image as the base image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libpq-dev \
    cron \
    nano \
    gnupg \
    ffmpeg \
    supervisor \
    lsb-release

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath

# Install Redis extension for PHP
RUN pecl install redis && docker-php-ext-enable redis

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN npm install -g nodemon pm2

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install -y procps

RUN composer install --optimize-autoloader --no-interaction
RUN npm install

# Copy the application files to the container
COPY . .
COPY ./docker/php.ini /usr/local/etc/php/conf.d/php-dev.ini

# Set permissions for Laravel
RUN chgrp -R www-data storage bootstrap/cache
RUN chmod -R ug+rwx storage bootstrap/cache

# Ensure the www-data user owns the relevant directories
RUN chown -R www-data:www-data storage bootstrap/cache

# Create a configuration directory for PsySH
RUN mkdir -p ~/.config/psysh
RUN chmod -R 755 ~/.config
RUN chown -R www-data:www-data ~/.config

RUN chown -R www-data:www-data storage/app

# Expose the necessary port (adjust as needed)
EXPOSE 3000
EXPOSE 80
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
