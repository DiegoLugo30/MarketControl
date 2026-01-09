#!/bin/bash

set -e

echo "🚀 Iniciando aplicación POS Barcode..."

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
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
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

# Iniciar PHP-FPM en segundo plano
echo "🐘 Iniciando PHP-FPM..."
php-fpm -D

# Dar tiempo a PHP-FPM para iniciar
sleep 2

# Verificar que PHP-FPM esté corriendo
if ! pgrep -x php-fpm > /dev/null; then
    echo "❌ Error: PHP-FPM no se inició correctamente"
    exit 1
fi

echo "✅ PHP-FPM corriendo"

# Iniciar Nginx en primer plano
echo "🌍 Iniciando Nginx..."
exec nginx -g 'daemon off;'
