FROM dunglas/frankenphp:php8.2

WORKDIR /app

COPY . .

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip nodejs npm \
    && docker-php-ext-install pdo pdo_mysql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader

# 🔥 IMPORTANTE: evitar modo Vite DEV
RUN rm -f public/hot

# 🔥 Forzar modo producción para Vite
ENV NODE_ENV=production

# Instalar frontend y compilar
RUN npm install
RUN npm run build

# Preparar Laravel
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 🔥 LIMPIAR Y CACHEAR BIEN (clave)
RUN php artisan config:clear \
 && php artisan cache:clear \
 && php artisan view:clear \
 && php artisan route:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 8080

# Servidor
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]