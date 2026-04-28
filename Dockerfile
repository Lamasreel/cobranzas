FROM dunglas/frankenphp:php8.2

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip curl \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN rm -f public/hot

RUN npm ci --include=dev
RUN npm run build
RUN npm prune --omit=dev

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD sh -c "rm -f public/hot && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php -S 0.0.0.0:${PORT:-8080} -t public"