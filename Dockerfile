FROM php:8.2-fpm

# Extensiones y herramientas necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip

# Node.js 18 para Vite
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Extensiones PHP
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /app

# Copiar proyecto
COPY . .

# Dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Construcción Vite
RUN npm ci && npm run build

# Permisos (Render sí permite estas rutas)
RUN chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache \
    && chmod -R 755 public

# Puerto asignado por Render
ENV PORT=10000

EXPOSE 10000

# Comando de ejecución
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
