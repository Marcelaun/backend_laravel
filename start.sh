#!/usr/bin/env sh
set -e

# 1. Limpa o cache do Laravel e gera a APP_KEY se necessário
php artisan key:generate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 2. Roda as migrações para o Supabase
echo "Running database migrations..."
php artisan migrate --force

# 3. Inicia o Nginx e o PHP-FPM (servidor de produção)
echo "Starting production servers..."
nginx -g 'daemon off;' &
php-fpm -F
