#!/bin/bash

set -e

echo "============================================"
echo "Iniciando aplicación Laravel..."
echo "============================================"

# Mostrar variables de entorno (sin mostrar contraseñas)
echo "APP_ENV: $APP_ENV"
echo "DATABASE_URL configurado: ${DATABASE_URL:0:20}..."

# Esperar a que la base de datos esté lista
echo "Esperando a que la base de datos esté lista..."
sleep 10

# Limpiar caché antes de cualquier cosa
echo "Limpiando caché..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force || echo "ERROR: Migraciones fallaron"

# Crear storage link
echo "Creando storage link..."
php artisan storage:link --force || true

# Optimizar después de migrar
echo "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "============================================"
echo "Configuración completada. Iniciando servicios..."
echo "============================================"

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf