@echo off
chcp 65001 >nul
echo ================================================
echo    DIAGNÓSTICO COMPLETO - POS BARCODE
echo ================================================
echo.

echo [1/7] Verificando Docker...
docker --version
if errorlevel 1 (
    echo ❌ Docker no está instalado
    pause
    exit /b 1
)
echo ✅ Docker instalado
echo.

echo [2/7] Estado de contenedores...
docker-compose ps
echo.

echo [3/7] Verificando si los contenedores están corriendo...
docker-compose ps | findstr "Up"
if errorlevel 1 (
    echo ❌ Los contenedores no están corriendo
    echo.
    echo Iniciando contenedores...
    docker-compose up -d
    timeout /t 10 >nul
)
echo.

echo [4/7] Logs del contenedor APP (últimas 50 líneas)...
echo ================================================
docker-compose logs --tail=50 app
echo ================================================
echo.

echo [5/7] Verificando dentro del contenedor...
echo ================================================
docker-compose exec -T app bash -c "echo '=== PHP Version ==='; php -v; echo ''; echo '=== Laravel Version ==='; php artisan --version 2>&1 || echo 'Laravel no disponible'; echo ''; echo '=== PHP-FPM Status ==='; ps aux | grep php-fpm | grep -v grep || echo 'PHP-FPM no corriendo'; echo ''; echo '=== .env exists? ==='; ls -la .env 2>&1 || echo '.env no existe'; echo ''; echo '=== Vendor exists? ==='; ls -la vendor 2>&1 | head -5 || echo 'vendor/ no existe';"
echo ================================================
echo.

echo [6/7] Verificando conexión a base de datos...
echo ================================================
docker-compose exec -T db psql -U pos_user -d pos_barcode -c "SELECT 1 as test;" 2>&1
if errorlevel 1 (
    echo ❌ No se puede conectar a la base de datos
) else (
    echo ✅ Conexión a BD exitosa
)
echo ================================================
echo.

echo [7/7] Probando endpoint...
echo ================================================
curl -I http://localhost:8000 2>&1
echo ================================================
echo.

echo ================================================
echo    RESUMEN
echo ================================================
echo.
echo URL de la aplicación: http://localhost:8000
echo.
echo Comandos útiles:
echo   docker-compose logs -f app     Ver logs en vivo
echo   docker-compose exec app bash   Entrar al contenedor
echo   docker-compose restart app     Reiniciar app
echo.
pause
