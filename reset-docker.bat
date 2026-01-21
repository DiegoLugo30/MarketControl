@echo off
chcp 65001 >nul
echo ================================================
echo    POS BARCODE - Limpiar y Reiniciar Docker
echo ================================================
echo.
echo ⚠️  ADVERTENCIA: Esto eliminará todos los contenedores
echo    y volúmenes (incluyendo la base de datos).
echo.
echo    Solo usa este comando si tienes problemas
echo    y quieres empezar desde cero.
echo.
pause
echo.

echo 🧹 Deteniendo contenedores...
docker-compose down -v

echo.
echo 🗑️  Eliminando imágenes del proyecto...
docker rmi pos-barcode-app 2>nul

echo.
echo ✅ Limpieza completada
echo.
echo Para iniciar de nuevo, ejecuta:
echo   start-docker.bat
echo.
pause
