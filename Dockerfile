FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader

RUN rm -f public/hot

RUN npm ci --include=dev
RUN npm run build

ENV NODE_ENV=production
RUN npm prune --omit=dev

RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN php artisan config:clear \
 && php artisan cache:clear \
 && php artisan view:clear \
 && php artisan route:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]