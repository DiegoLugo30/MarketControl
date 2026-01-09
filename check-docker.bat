@echo off
chcp 65001 >nul
echo ================================================
echo    POS BARCODE - Diagnóstico Docker
echo ================================================
echo.

echo 📊 Estado de los contenedores:
echo.
docker-compose ps
echo.

echo 📋 Logs del contenedor app (últimas 50 líneas):
echo.
docker-compose logs --tail=50 app
echo.

echo 📋 Logs del contenedor nginx (últimas 20 líneas):
echo.
docker-compose logs --tail=20 nginx
echo.

echo 📋 Logs del contenedor db (últimas 20 líneas):
echo.
docker-compose logs --tail=20 db
echo.

echo ================================================
echo.
echo Para ver logs en tiempo real: docker-compose logs -f
echo.
pause
