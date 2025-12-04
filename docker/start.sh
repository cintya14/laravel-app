#!/bin/bash

set -e

echo "============================================"
echo "Iniciando aplicación Laravel en Render..."
echo "============================================"

# Mostrar información de entorno
echo "APP_ENV: $APP_ENV"
echo "APP_URL: ${APP_URL:-No configurado}"
echo "VITE_APP_URL: ${VITE_APP_URL:-No configurado}"

# Verificar si los assets fueron construidos
echo "Verificando assets de Vite..."
if [ ! -f /var/www/public/build/manifest.json ]; then
    echo "ERROR: manifest.json no encontrado!"
    echo "Contenido de /var/www/public/build/:"
    ls -la /var/www/public/build/ 2>/dev/null || echo "Directorio no existe"
    
    echo "Intentando construir assets..."
    cd /var/www && npm run build 2>&1 | tail -20
    
    # Verificar nuevamente
    if [ ! -f /var/www/public/build/manifest.json ]; then
        echo "FALLO CRÍTICO: No se pudieron construir los assets"
        exit 1
    fi
else
    echo "✓ Assets encontrados en /var/www/public/build/"
    echo "Manifest:"
    head -5 /var/www/public/build/manifest.json
fi

# Limpiar cachés
echo "Limpiando cachés de Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache para producción
echo "Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link
echo "Creando storage link..."
php artisan storage:link --force

echo "============================================"
echo "Iniciando servicios..."
echo "============================================"

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf