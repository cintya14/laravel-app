FROM php:8.2-fpm

# 1. Instalar dependencias del sistema + Node.js 18.x
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update && apt-get install -y \
    nodejs \
    nginx \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean

# 2. Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 3. Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Establecer directorio de trabajo
WORKDIR /var/www

# 5. Copiar archivos de dependencias primero para cachear capas
COPY package*.json ./
COPY composer.json composer.lock ./

# 6. Instalar dependencias de Node.js
RUN npm ci --only=production

# 7. Instalar dependencias de PHP (sin desarrollo)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Copiar el resto de la aplicación
COPY . .

# 9. Construir los assets de Vite para producción
RUN npm run build

# 10. Establecer permisos correctos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# 11. Copiar configuración de Nginx y Supervisor
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 12. Exponer el puerto 8080 (Render usa este puerto por defecto para aplicaciones web)
EXPOSE 8080

# 13. Copiar script de inicio y dar permisos de ejecución
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# 14. Comando de inicio
CMD ["/start.sh"]