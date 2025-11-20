# --- STAGE 1: BUILD ---
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize

# --- STAGE 2: PRODUCTION ---
# Usamos a imagem CLI, que é mais leve e feita para rodar comandos como 'artisan serve'
FROM php:8.2-cli-alpine

# Instala pacotes do sistema necessários para o Laravel e PostgreSQL
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    postgresql-dev \
    oniguruma-dev

# Instala extensões PHP
RUN docker-php-ext-install pdo_pgsql mbstring zip gd opcache

# Configura diretório de trabalho
WORKDIR /var/www/html

# Copia o código do estágio de build
COPY --from=vendor /app /var/www/html

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copia o script de inicialização
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
