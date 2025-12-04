#!/bin/bash

# Esperar a que la base de datos esté lista
echo "Esperando a que la base de datos esté lista..."
sleep 5

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Generar key si no existe
echo "Verificando APP_KEY..."
php artisan key:generate --force --no-interaction

# Crear storage link
echo "Creando storage link..."
php artisan storage:link --force

# Limpiar y optimizar
echo "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisor
echo "Iniciando servicios..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf