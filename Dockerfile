FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

# Dependencias sistema (SIN nodejs viejo)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql

# Node 20 (IMPORTANTE)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader

# Evitar Vite dev server
RUN rm -f public/hot
ENV NODE_ENV=production

# Frontend build
RUN npm ci
RUN npm run build

# Permisos Laravel
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache Laravel
RUN php artisan config:clear \
 && php artisan cache:clear \
 && php artisan view:clear \
 && php artisan route:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]