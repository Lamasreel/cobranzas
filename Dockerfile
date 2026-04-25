FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader

# Node + Vite
RUN apt-get update && apt-get install -y nodejs npm
RUN npm install
RUN npm run build

# Preparar Laravel
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache de Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan cache:clear \

EXPOSE 8080 

# IMPORTANTE: usar shell para que tome $PORT
CMD sh -c "php -S 0.0.0.0:${PORT:-8080} -t public"