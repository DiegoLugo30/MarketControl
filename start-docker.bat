@echo off
chcp 65001 >nul
echo ================================================
echo    POS BARCODE - Inicio con Docker
echo ================================================
echo.

REM Verificar si Docker está instalado
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ ERROR: Docker no está instalado
    echo.
    echo Por favor instala Docker Desktop desde:
    echo https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo ✅ Docker detectado
echo.

REM Verificar si Docker está corriendo
docker ps >nul 2>&1
if errorlevel 1 (
    echo ❌ ERROR: Docker no está corriendo
    echo.
    echo Por favor inicia Docker Desktop y vuelve a ejecutar este script
    pause
    exit /b 1
)

echo ✅ Docker está corriendo
echo.

REM Verificar si existe .env
if not exist .env (
    echo 📝 Creando archivo .env desde .env.docker...
    copy .env.docker .env >nul
    echo ✅ Archivo .env creado
    echo.
)

echo 🐳 Levantando contenedores...
echo.
docker-compose up -d --build

if errorlevel 1 (
    echo.
    echo ❌ ERROR al levantar los contenedores
    echo.
    echo Revisa los logs con: docker-compose logs
    pause
    exit /b 1
)

echo.
echo ================================================
echo    ✅ Aplicación iniciada correctamente
echo ================================================
echo.
echo 🌐 URL: http://localhost:8000
echo.
echo Comandos útiles:
echo   - Ver logs:          docker-compose logs -f
echo   - Detener:           docker-compose down
echo   - Reiniciar:         docker-compose restart
echo   - Ver estado:        docker-compose ps
echo.
echo Abriendo navegador...
timeout /t 3 >nul
start http://localhost:8000

pause
