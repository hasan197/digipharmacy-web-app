FROM php:8.1-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    procps \
    lsof \
    net-tools \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql bcmath opcache

# Install additional PHP extensions
RUN pecl install redis \
    && docker-php-ext-enable redis

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 512M" >> "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = 64M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 64M" >> "$PHP_INI_DIR/php.ini"

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-interaction --no-plugins

# Install Node.js dependencies
RUN npm install

# Generate JWT secret if not exists
RUN php artisan jwt:secret --force

# Expose ports
EXPOSE 8000 5173

# Simple CMD without environment check
CMD php artisan serve --host=0.0.0.0 --port=8000