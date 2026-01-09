# Comandos Útiles - Sistema POS Barcode

## Comandos de Instalación

```bash
# Instalar dependencias de Composer
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Ejecutar migraciones desde cero (borra todo)
php artisan migrate:fresh
```

## Comandos de Desarrollo

```bash
# Iniciar servidor de desarrollo
php artisan serve

# Iniciar servidor en puerto específico
php artisan serve --port=8080

# Iniciar servidor accesible desde la red local
php artisan serve --host=0.0.0.0 --port=8000
```

## Comandos de Base de Datos

```bash
# Ver estado de migraciones
php artisan migrate:status

# Revertir última migración
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset

# Ejecutar migraciones pendientes
php artisan migrate

# Limpiar y re-ejecutar todas las migraciones
php artisan migrate:fresh

# Limpiar y re-ejecutar con seeders
php artisan migrate:fresh --seed
```

## Comandos de Caché

```bash
# Limpiar caché de configuración
php artisan config:clear

# Cachear configuración (producción)
php artisan config:cache

# Limpiar caché de rutas
php artisan route:clear

# Cachear rutas (producción)
php artisan route:cache

# Limpiar caché de vistas
php artisan view:clear

# Limpiar toda la caché de la aplicación
php artisan cache:clear

# Limpiar todo (config, routes, views, cache)
php artisan optimize:clear
```

## Comandos de Información

```bash
# Ver todas las rutas registradas
php artisan route:list

# Ver rutas específicas
php artisan route:list --name=products

# Ver información del sistema
php artisan about

# Listar comandos disponibles
php artisan list
```

## Comandos de Generación de Código

```bash
# Crear un nuevo controlador
php artisan make:controller NombreController

# Crear un nuevo modelo
php artisan make:model NombreModelo

# Crear modelo con migración
php artisan make:model NombreModelo -m

# Crear migración
php artisan make:migration create_nombre_table

# Crear seeder
php artisan make:seeder NombreSeeder

# Crear middleware
php artisan make:middleware NombreMiddleware

# Crear request (validación)
php artisan make:request NombreRequest
```

## Comandos de Testing

```bash
# Ejecutar tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage
```

## Comandos de Mantenimiento

```bash
# Poner aplicación en modo mantenimiento
php artisan down

# Mensaje personalizado en mantenimiento
php artisan down --message="Actualización en proceso"

# Sacar aplicación del modo mantenimiento
php artisan up
```

## PostgreSQL - Comandos de Terminal

```bash
# Conectar a PostgreSQL
psql -U postgres

# Conectar a base de datos específica
psql -U pos_user -d pos_barcode

# Listar bases de datos
\l

# Conectar a una base de datos
\c pos_barcode

# Listar tablas
\dt

# Describir una tabla
\d products

# Ejecutar consulta SQL
SELECT * FROM products;

# Salir de psql
\q
```

## PostgreSQL - Consultas SQL Útiles

```sql
-- Ver todos los productos
SELECT * FROM products;

-- Ver productos con bajo stock
SELECT * FROM products WHERE stock < 10;

-- Ver ventas del día
SELECT * FROM sales WHERE DATE(created_at) = CURRENT_DATE;

-- Total de ventas del día
SELECT SUM(total) FROM sales WHERE DATE(created_at) = CURRENT_DATE;

-- Productos más vendidos
SELECT p.name, SUM(si.quantity) as total_vendido
FROM products p
JOIN sale_items si ON p.id = si.product_id
GROUP BY p.id, p.name
ORDER BY total_vendido DESC
LIMIT 10;

-- Actualizar stock de un producto
UPDATE products SET stock = 100 WHERE barcode = '7790040258501';

-- Eliminar todas las ventas (cuidado!)
DELETE FROM sale_items;
DELETE FROM sales;

-- Resetear secuencias después de borrar datos
ALTER SEQUENCE sales_id_seq RESTART WITH 1;
ALTER SEQUENCE sale_items_id_seq RESTART WITH 1;
```

## Composer - Comandos Útiles

```bash
# Actualizar dependencias
composer update

# Actualizar dependencia específica
composer update laravel/framework

# Instalar nueva dependencia
composer require nombre/paquete

# Remover dependencia
composer remove nombre/paquete

# Autoload dump
composer dump-autoload

# Verificar dependencias
composer validate

# Ver dependencias instaladas
composer show

# Ver información de un paquete
composer show laravel/framework
```

## Git - Control de Versiones

```bash
# Inicializar repositorio
git init

# Ver estado
git status

# Agregar todos los archivos
git add .

# Commit con mensaje
git commit -m "Implementación inicial del sistema POS"

# Ver historial
git log --oneline

# Crear rama
git checkout -b feature/nueva-funcionalidad

# Cambiar de rama
git checkout main

# Ver ramas
git branch

# Fusionar rama
git merge feature/nueva-funcionalidad
```

## NPM - Manejo de Assets (Opcional)

Si decides usar Vite para compilar assets:

```bash
# Instalar dependencias
npm install

# Compilar assets para desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Observar cambios (hot reload)
npm run watch
```

## Backup de Base de Datos

```bash
# Crear backup
pg_dump -U pos_user -d pos_barcode > backup_pos_$(date +%Y%m%d).sql

# Crear backup comprimido
pg_dump -U pos_user -d pos_barcode | gzip > backup_pos_$(date +%Y%m%d).sql.gz

# Restaurar desde backup
psql -U pos_user -d pos_barcode < backup_pos_20240101.sql

# Restaurar desde backup comprimido
gunzip -c backup_pos_20240101.sql.gz | psql -U pos_user -d pos_barcode
```

## Permisos (Linux/Mac)

```bash
# Dar permisos de escritura a storage y cache
chmod -R 775 storage bootstrap/cache

# Dar permisos al usuario web
sudo chown -R www-data:www-data storage bootstrap/cache

# Hacer ejecutable artisan
chmod +x artisan
```

## Variables de Entorno

```bash
# Ver variable de entorno
echo $APP_ENV

# Setear variable temporalmente
export APP_ENV=local

# Ver todas las variables
printenv | grep APP_
```

## Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver últimas 100 líneas de logs
tail -n 100 storage/logs/laravel.log

# Buscar errores en logs
grep "ERROR" storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log
```

## Performance

```bash
# Optimizar aplicación para producción
php artisan optimize

# Cache de configuración
php artisan config:cache

# Cache de rutas
php artisan route:cache

# Cache de vistas
php artisan view:cache

# Limpiar todo y re-optimizar
php artisan optimize:clear && php artisan optimize
```

## Debugging

```bash
# Habilitar modo debug
# En .env: APP_DEBUG=true

# Ver consultas SQL en el código
DB::enableQueryLog();
// tu código
dd(DB::getQueryLog());

# Usar Tinker (REPL de Laravel)
php artisan tinker

# Dentro de Tinker:
>>> App\Models\Product::count()
>>> App\Models\Product::where('stock', '<', 10)->get()
>>> exit
```

## Producción

```bash
# Optimizar para producción
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Deshabilitar debug
# En .env: APP_DEBUG=false

# Generar clave nueva (¡cuidado! invalida sesiones)
php artisan key:generate
```
