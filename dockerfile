FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader

RUN php artisan config:cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:${PORT:-8080}", "-t", "public"]