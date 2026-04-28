FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install 

# Eliminar Vite dev
RUN rm -f public/hot

# Build frontend
RUN npm ci --include=dev
RUN npm run build
RUN npm prune --omit=dev

# Permisos Laravel
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD sh -c "rm -f public/hot && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php -S 0.0.0.0:${PORT:-8080} -t public"