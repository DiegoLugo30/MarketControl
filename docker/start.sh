#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel en Railway"
echo "PORT: ${PORT}"

cd /var/www

echo "⚙️ Configurando PHP-FPM..."
sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

echo "🐘 Iniciando PHP-FPM..."
php-fpm -F &

sleep 2

if ! pgrep -x php-fpm > /dev/null; then
  echo "❌ PHP-FPM no está corriendo"
  exit 1
fi

echo "✅ PHP-FPM OK"

echo "🌍 Configurando Nginx PORT..."
envsubst '$PORT' < /etc/nginx/conf.d/default.conf > /tmp/default.conf
mv /tmp/default.conf /etc/nginx/conf.d/default.conf

echo "🌐 Iniciando Nginx..."
exec nginx -g 'daemon off;'
