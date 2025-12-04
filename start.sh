#!/bin/bash

echo "=== INICIANDO LARAVEL ==="

# Verificar si PHP está instalado
php --version

# Verificar assets
if [ ! -f /app/public/build/manifest.json ]; then
    echo "Construyendo assets..."
    npm run build
fi

# Configurar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force

echo "=== INICIANDO SERVIDOR EN PUERTO $PORT ==="

# Iniciar servidor PHP
exec php -S 0.0.0.0:$PORT -t public