FROM php:8.2-fpm

# === 1. Instalar Node.js ANTES que cualquier cosa ===
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update && apt-get install -y \
    nodejs \
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
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Verificar Node.js instalado
RUN node --version && npm --version

# Extensiones PHP
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

# Directorio de trabajo
WORKDIR /var/www

# === 2. Copiar solo package.json PRIMERO para caché eficiente ===
COPY package*.json ./

# === 3. Instalar dependencias Node.js (esto NO está en caché) ===
RUN npm ci --only=production

# === 4. Copiar el resto de la aplicación ===
COPY . .

# === 5. Construir assets CON variables de entorno ===
# Configurar variables para el build
ARG VITE_APP_URL=${VITE_APP_URL:-https://localhost}
ENV VITE_APP_URL=${VITE_APP_URL}

RUN echo "Building assets with VITE_APP_URL: $VITE_APP_URL" && \
    npm run build

# === 6. Instalar dependencias PHP ===
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public

# Configuración
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]