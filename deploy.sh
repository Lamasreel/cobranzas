#!/bin/bash

echo "#######################################"
echo "# Iniciando actualización (Cobranzas) #"
echo "#######################################"

# 1. Moverse a la carpeta REAL del proyecto en tu Contabo
cd /var/www/cobranzas

# 2. Activar el modo mantenimiento de Laravel
echo "Poniendo la aplicación en modo mantenimiento..."
php artisan down

# 3. Descargar los últimos cambios desde la rama correcta (master)
echo "Descargando cambios desde Git..."
git pull origin main

# 4. Instalar dependencias de PHP optimizando el autoloader sin interacción
echo "Actualizando dependencias de PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Ejecutar migraciones de base de datos de forma segura
echo "Ejecutando migraciones pendientes..."
php artisan migrate --force

# 6. Limpiar y regenerar la caché de producción
echo "Optimizando la caché de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Volver a encender la aplicación
echo "Desactivando modo mantenimiento..."
php artisan up

echo "#######################################"
echo "# ¡Despliegue finalizado con éxito!   #"
echo "#######################################"
