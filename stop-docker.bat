@echo off
chcp 65001 >nul
echo ================================================
echo    POS BARCODE - Detener Docker
echo ================================================
echo.

docker-compose down

if errorlevel 1 (
    echo ❌ ERROR al detener los contenedores
    pause
    exit /b 1
)

echo.
echo ✅ Contenedores detenidos correctamente
echo.
pause
