FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
    libicu-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-scripts

COPY . .

RUN APP_KEY=base64:4XWVeGm49Ph+8r2lYbE3LBM/GL3dJ4Y9xpE8qBvVkJ0= APP_ENV=production \
    composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p \
        storage/logs \
        storage/app/public \
        storage/app/templates \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && printf '[www]\nlisten = 0.0.0.0:9000\nclear_env = no\ncatch_workers_output = yes\n' \
        > /usr/local/etc/php-fpm.d/zz-formulario-rh.conf

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/entrypoint.sh /usr/local/bin/formulario-entrypoint

RUN chmod +x /usr/local/bin/formulario-entrypoint \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["formulario-entrypoint"]
CMD ["php-fpm", "-F"]
