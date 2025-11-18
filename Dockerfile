# Etapa 1 — PHP com extensões
FROM php:8.2-fpm

# Instalar dependências de sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar código
WORKDIR /var/www/html
COPY . .

# Instalar dependências Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissões storage e cache
RUN chmod -R 777 storage bootstrap/cache

# Porta obrigatória para Render
EXPOSE 10000

# Comando de inicialização
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
