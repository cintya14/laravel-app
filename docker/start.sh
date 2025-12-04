#!/bin/bash

set -e

echo "============================================"
echo "INICIANDO LARAVEL EN RENDER..."
echo "============================================"

# Mostrar algunas variables (sin passwords)
echo "APP_URL: ${APP_URL}"
echo "APP_ENV: ${APP_ENV}"
echo "DB_HOST: ${DB_HOST}"

# Verificar si los assets se construyeron durante el build
# Si no, construirlos ahora
if [ ! -f /var/www/public/build/manifest.json ]; then
    echo "Assets no encontrados. Construyendo Vite..."
    cd /var/www
    npm install --silent
    npm run build --silent
    echo "✓ Assets construidos"
fi

# Verificar que el manifest existe
if [ -f /var/www/public/build/manifest.json ]; then
    echo "✓ Vite manifest encontrado"
else
    echo "✗ ERROR: No hay manifest.json"
    exit 1
fi

# Configurar Laravel para producción
echo "Configurando Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link --force

echo "============================================"
echo "INICIANDO SERVICIOS..."
echo "============================================"

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf