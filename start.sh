#!/bin/bash
# start.sh

echo "Verificando si hay assets construidos..."
if [ -d /tmp/railway-build-assets ]; then
    echo "Copiando assets construidos a public/build..."
    cp -r /tmp/railway-build-assets public/build
    echo "✓ Assets copiados"
else
    echo "✗ No hay assets construidos. Construyendo..."
    npm install && npm run build
fi

echo "Iniciando aplicación Laravel..."
php artisan serve --host=0.0.0.0 --port=$PORT