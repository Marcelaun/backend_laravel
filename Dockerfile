# --- STAGE 1: BUILD & DEPENDENCIES ---
FROM composer:2.7 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
COPY . .
RUN composer install --no-dev --optimize-autoloader

# --- STAGE 2: PRODUCTION (Nginx + PHP-FPM) ---
FROM php:8.2-fpm-alpine AS laravel

# Instalação de pacotes de sistema (Git, Nginx, PostgreSQL, libs)
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    tzdata \
    && rm -rf /var/cache/apk/*

# Instala extensões PHP (pdo_pgsql para Supabase)
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip gd opcache

# Configuração do Nginx e FPM
COPY .docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY .docker/fpm/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Copiar código e vendor do estágio anterior
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor /var/www/html/vendor

# Permissões: O Render/Nginx/FPM roda como www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copia e dá permissão ao script de entrada
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

# Comando de inicialização
CMD ["/usr/local/bin/start.sh"]
