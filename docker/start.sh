#!/bin/bash
set -e

echo "🚄 Railway booting Laravel..."

cd /var/www

# Crear .env si no existe
if [ ! -f ".env" ]; then
  cp .env.docker .env
fi

# Generar APP_KEY si no existe
php artisan key:generate --force || true

# Ejecutar migraciones
php artisan migrate --force || true

echo "🚀 Starting Laravel on port $PORT"

exec php artisan serve --host=0.0.0.0 --port=$PORT

