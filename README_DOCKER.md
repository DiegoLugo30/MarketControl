# Sistema POS Barcode - Instalación con Docker 🐳

Esta guía te permitirá levantar toda la aplicación con un solo comando usando Docker. **No necesitas instalar PHP, PostgreSQL ni Composer manualmente.**

## 📋 Requisitos Previos

Solo necesitas tener instalado:

- **Docker Desktop** (incluye Docker y Docker Compose)
  - Windows: https://www.docker.com/products/docker-desktop
  - Mac: https://www.docker.com/products/docker-desktop
  - Linux: https://docs.docker.com/engine/install/

Para verificar que Docker está instalado:

```bash
docker --version
docker-compose --version
```

## 🚀 Instalación Rápida (3 Pasos)

### 1. Clonar o Abrir el Proyecto

Si ya tienes el proyecto, abre una terminal en la carpeta:

```bash
cd C:\Users\dlugo\untitled
```

### 2. Configurar Variables de Entorno (Opcional)

El proyecto ya incluye configuración por defecto. Si quieres personalizarla:

```bash
# Copiar archivo de configuración Docker
copy .env.docker .env

# O editar las variables en docker-compose.yml
```

Variables por defecto:
- **Base de datos**: pos_barcode
- **Usuario**: pos_user
- **Contraseña**: pos_password
- **Puerto web**: 8000

### 3. Levantar los Contenedores

```bash
docker-compose up -d --build
```

Este comando:
- ✅ Descarga las imágenes necesarias (primera vez)
- ✅ Construye la imagen de Laravel
- ✅ Crea el contenedor de PostgreSQL
- ✅ Crea el contenedor de Nginx
- ✅ Instala todas las dependencias
- ✅ Ejecuta las migraciones automáticamente
- ✅ Configura todo lo necesario

**Tiempo estimado**: 2-5 minutos la primera vez

## 🌐 Acceder a la Aplicación

Una vez que los contenedores estén corriendo, abre tu navegador:

**http://localhost:8000**

¡Listo! La aplicación está funcionando. 🎉

## 📦 Contenedores Incluidos

El sistema levanta 3 contenedores:

1. **pos-barcode-app** - Laravel + PHP 8.2 + PHP-FPM
2. **pos-barcode-nginx** - Servidor web Nginx
3. **pos-barcode-db** - Base de datos PostgreSQL 15

## 🛠️ Comandos Útiles

### Ver estado de los contenedores

```bash
docker-compose ps
```

### Ver logs en tiempo real

```bash
# Todos los contenedores
docker-compose logs -f

# Solo la aplicación
docker-compose logs -f app

# Solo la base de datos
docker-compose logs -f db

# Solo Nginx
docker-compose logs -f nginx
```

### Detener los contenedores

```bash
docker-compose stop
```

### Iniciar los contenedores (si ya están creados)

```bash
docker-compose start
```

### Reiniciar los contenedores

```bash
docker-compose restart
```

### Detener y eliminar contenedores

```bash
docker-compose down
```

### Detener, eliminar contenedores y volúmenes (¡cuidado! borra la BD)

```bash
docker-compose down -v
```

### Reconstruir los contenedores

```bash
docker-compose up -d --build
```

## 🔧 Ejecutar Comandos Laravel

### Ejecutar comandos de Artisan

```bash
# Sintaxis general
docker-compose exec app php artisan [comando]

# Ejemplos:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:list
```

### Acceder a la consola de Laravel (Tinker)

```bash
docker-compose exec app php artisan tinker
```

### Ejecutar Composer

```bash
docker-compose exec app composer install
docker-compose exec app composer update
docker-compose exec app composer require [paquete]
```

### Acceder a la terminal del contenedor

```bash
docker-compose exec app bash
```

Una vez dentro, puedes ejecutar cualquier comando como si estuvieras en tu máquina local.

## 🗄️ Gestionar la Base de Datos

### Conectar a PostgreSQL desde la terminal

```bash
docker-compose exec db psql -U pos_user -d pos_barcode
```

### Ejecutar consultas SQL

```bash
# Ver todos los productos
docker-compose exec db psql -U pos_user -d pos_barcode -c "SELECT * FROM products;"

# Ver ventas del día
docker-compose exec db psql -U pos_user -d pos_barcode -c "SELECT * FROM sales WHERE DATE(created_at) = CURRENT_DATE;"
```

### Backup de la base de datos

```bash
# Crear backup
docker-compose exec db pg_dump -U pos_user pos_barcode > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker-compose exec -T db psql -U pos_user -d pos_barcode < backup_20240101.sql
```

### Resetear la base de datos

```bash
docker-compose exec app php artisan migrate:fresh
```

## 🐛 Solución de Problemas

### Puerto 8000 ya está en uso

Si ves el error: `Bind for 0.0.0.0:8000 failed: port is already allocated`

**Opción 1**: Cambiar el puerto en `docker-compose.yml`

```yaml
nginx:
  ports:
    - "8080:80"  # Cambiar 8000 por 8080
```

Luego accede a: http://localhost:8080

**Opción 2**: Detener el proceso que usa el puerto 8000

```bash
# Windows
netstat -ano | findstr :8000
taskkill /PID [número] /F

# Linux/Mac
lsof -ti:8000 | xargs kill -9
```

### Error "permission denied" en Windows

Si Docker te da errores de permisos:

1. Asegúrate de que Docker Desktop esté corriendo
2. Ejecuta PowerShell o CMD como Administrador
3. En Docker Desktop → Settings → Resources → File sharing, agrega la carpeta del proyecto

### Los contenedores no inician

```bash
# Ver logs detallados
docker-compose logs

# Limpiar todo y empezar de nuevo
docker-compose down -v
docker system prune -a
docker-compose up -d --build
```

### La aplicación no carga (error 502)

```bash
# Ver logs de la aplicación
docker-compose logs app

# Reiniciar contenedor de la app
docker-compose restart app
```

### Error de conexión a base de datos

```bash
# Verificar que PostgreSQL esté corriendo
docker-compose ps db

# Ver logs de PostgreSQL
docker-compose logs db

# Reiniciar PostgreSQL
docker-compose restart db
```

### Cambios en el código no se reflejan

Los cambios en archivos PHP, Blade, CSS y JS se reflejan automáticamente porque la carpeta está montada como volumen. Si no ves los cambios:

```bash
# Limpiar cache de Laravel
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan config:clear

# O simplemente:
docker-compose exec app php artisan optimize:clear
```

## 📂 Estructura de Archivos Docker

```
proyecto/
├── docker/
│   ├── nginx/
│   │   ├── default.conf      # Configuración de Nginx
│   │   └── nginx.conf        # Configuración principal Nginx
│   ├── php/
│   │   └── local.ini         # Configuración de PHP
│   └── start.sh              # Script de inicialización
├── Dockerfile                 # Imagen de Laravel
├── docker-compose.yml         # Orquestación de servicios
└── .env.docker               # Variables de entorno Docker
```

## 🔐 Seguridad

### Cambiar contraseña de PostgreSQL

Edita `docker-compose.yml`:

```yaml
db:
  environment:
    POSTGRES_PASSWORD: tu_nueva_contraseña_segura
```

También actualiza `.env`:

```env
DB_PASSWORD=tu_nueva_contraseña_segura
```

Luego recrea los contenedores:

```bash
docker-compose down -v
docker-compose up -d
```

### Usar en Producción

Si vas a usar Docker en producción:

1. Cambia `APP_ENV=production` y `APP_DEBUG=false` en `.env`
2. Usa contraseñas seguras
3. Configura SSL/HTTPS
4. Usa volúmenes nombrados para persistencia
5. Implementa backups automáticos

## 📊 Monitoreo

### Ver uso de recursos

```bash
docker stats
```

### Ver espacio usado por Docker

```bash
docker system df
```

### Limpiar imágenes y contenedores no usados

```bash
docker system prune -a
```

## 🚀 Despliegue a Producción

Para desplegar en un servidor:

```bash
# En el servidor
git clone [tu-repositorio]
cd pos-barcode

# Configurar producción
cp .env.docker .env
# Editar .env con valores de producción

# Levantar servicios
docker-compose up -d --build

# Ver logs
docker-compose logs -f
```

## 🆘 Ayuda Adicional

### Comandos de diagnóstico

```bash
# Ver información de Docker
docker info

# Ver imágenes descargadas
docker images

# Ver todos los contenedores (incluso detenidos)
docker ps -a

# Ver volúmenes
docker volume ls

# Inspeccionar contenedor
docker inspect pos-barcode-app
```

## 🎯 Ventajas de Docker

✅ **No necesitas instalar nada localmente** (solo Docker)
✅ **Mismo entorno para todos** (desarrollo = producción)
✅ **Fácil de resetear** (si algo falla, `docker-compose down` y listo)
✅ **Aislamiento completo** (no interfiere con otros proyectos)
✅ **Portable** (funciona en Windows, Mac, Linux)
✅ **Rápido de configurar** (todo en 1 comando)

## 📞 Soporte

Si tienes problemas:

1. Revisa los logs: `docker-compose logs`
2. Verifica que Docker Desktop esté corriendo
3. Asegúrate de tener espacio en disco
4. Intenta reconstruir: `docker-compose down -v && docker-compose up -d --build`

---

## 🎉 ¡Eso es Todo!

Con Docker, tienes un entorno completo y funcional con un solo comando. No más "en mi máquina funciona" 😄

**URL**: http://localhost:8000
**Base de datos**: PostgreSQL en puerto 5432
**Datos predeterminados**: pos_user / pos_password / pos_barcode
