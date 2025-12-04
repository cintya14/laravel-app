FROM php:8.2-fpm

# -------------------------
# Dependencias de sistema
# -------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    gnupg

# Node.js 18 (para compilar Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# -------------------------
# Extensiones PHP
# -------------------------
RUN docker-php-ext-configure intl
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Usuario
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel

WORKDIR /var/www

# Copiar todo el proyecto
COPY . /var/www

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------
# Construir assets con Vite
# -------------------------
RUN npm ci
RUN npm run build

# Permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache \
    && chmod -R 755 public

# -------------------------
# Nginx y Supervisor
# -------------------------
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
