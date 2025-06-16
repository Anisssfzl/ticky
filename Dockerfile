# Gunakan image PHP resmi dengan versi yang cocok
FROM php:8.2-fpm

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set workdir project Laravel
WORKDIR /var/www

# Copy semua file project Laravel
COPY . .

# Install dependency Laravel (pastikan composer.json ada)
RUN composer install --no-dev --optimize-autoloader

# Berikan permission pada folder storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port php-fpm
EXPOSE 9000

# Jalankan php-fpm
CMD ["php-fpm"]