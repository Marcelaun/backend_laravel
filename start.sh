#!/usr/bin/env sh
set -e

# 0. Garante que as pastas de cache existam (FIX para erro 'View path not found')
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# 1. Cache para performance
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Link do Storage
php artisan storage:link

# 3. Rodar Migrações (Essencial)
echo "Running migrations..."
php artisan migrate --force

# 4. Iniciar Servidor Laravel
# O Render define a variável $PORT automaticamente.
# Usamos 0.0.0.0 para aceitar conexões externas.
echo "Starting Laravel Server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
