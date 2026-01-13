#!/bin/bash

set -e

echo "🚀 Iniciando aplicación POS Barcode..."
echo "PORT en Railway: ${PORT}"

# Ir al directorio de la aplicación
cd /var/www

# Esperar a que PostgreSQL esté listo
echo "⏳ Esperando a PostgreSQL..."
for i in {1..30}; do
  if timeout 1 bash -c "cat < /dev/null > /dev/tcp/db/5432" 2>/dev/null; then
    echo "✅ PostgreSQL está listo!"
    sleep 2
    break
  fi
  echo "Esperando PostgreSQL... ($i/30)"
  sleep 2
done

# Instalar dependencias si no existen
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
fi

# Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.docker .env
fi

# Generar clave de aplicación si no existe
if [ "$APP_ENV" != "production" ]; then
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
    fi
fi

# Configurar permisos
echo "🔐 Configurando permisos..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force 2>/dev/null || echo "⚠️ Migraciones ya ejecutadas o error (continuando...)"

# Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

echo "✅ Aplicación lista!"
echo "🌐 Accede a: http://localhost:8000"

# ==============================
# Railway: usar servidor interno
# ==============================
if [ -n "$PORT" ]; then
    echo "🚄 Railway detectado - iniciando Laravel en puerto $PORT"
    exec php artisan serve --host=0.0.0.0 --port=$PORT
fi

# ==============================
# Local: Nginx + PHP-FPM
# ==============================
echo "🐘 Iniciando PHP-FPM..."
php-fpm -D

sleep 2

echo "🌍 Configurando puerto dinámico para Nginx..."
envsubst '$PORT' < /etc/nginx/conf.d/default.conf > /tmp/default.conf
mv /tmp/default.conf /etc/nginx/conf.d/default.conf

# Iniciar Nginx en primer plano
echo "🌍 Iniciando Nginx..."
exec nginx -g 'daemon off;'
