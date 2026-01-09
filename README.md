# Sistema de Punto de Venta con Lectura de Código de Barras

Aplicación web completa desarrollada en Laravel + PostgreSQL para gestión de ventas con lectura de código de barras (USB y cámara).

## 🚀 Inicio Rápido con Docker (Recomendado)

La forma más fácil y rápida de ejecutar la aplicación es usando Docker. **No necesitas instalar PHP, PostgreSQL ni Composer.**

### Requisitos

- **Solo Docker Desktop**: https://www.docker.com/products/docker-desktop

### Instalación en 2 Pasos

#### Windows
```bash
# 1. Doble clic en:
start-docker.bat

# O desde CMD/PowerShell:
docker-compose up -d --build
```

#### Linux/Mac
```bash
# Levantar contenedores
docker-compose up -d --build

# Ver logs
docker-compose logs -f
```

**¡Listo!** Abre tu navegador en: **http://localhost:8000**

📖 **Documentación completa de Docker**: Ver [README_DOCKER.md](README_DOCKER.md)

---

## 📦 Instalación Manual (Sin Docker)

Si prefieres instalar todo manualmente:

### Requisitos

- PHP 8.2 o superior
- Composer
- PostgreSQL 12 o superior
- Extensiones PHP: pdo_pgsql, mbstring, openssl, tokenizer

## Instalación

### 1. Clonar repositorio e instalar dependencias

```bash
# Instalar Laravel (si no tienes Composer instalado, ve a https://getcomposer.org)
composer create-project laravel/laravel .

# O si ya tienes el proyecto clonado:
composer install
```

### 2. Configurar base de datos PostgreSQL

Crear base de datos:

```sql
CREATE DATABASE pos_barcode;
CREATE USER pos_user WITH PASSWORD 'tu_password_seguro';
GRANT ALL PRIVILEGES ON DATABASE pos_barcode TO pos_user;
```

### 3. Configurar archivo .env

Copiar el archivo de ejemplo y configurar:

```bash
cp .env.example .env
```

Editar `.env` con los datos de PostgreSQL:

```env
APP_NAME="POS Barcode"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_barcode
DB_USERNAME=pos_user
DB_PASSWORD=tu_password_seguro

SESSION_DRIVER=file
```

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Iniciar servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: http://localhost:8000

## Características

### Lectura de Código de Barras

- **Lector USB**: Input con autofocus que detecta automáticamente el código al presionar ENTER
- **Cámara**: Acceso a cámara del dispositivo para escanear códigos EAN/UPC usando QuaggaJS

### Gestión de Productos

- Búsqueda automática en base de datos local
- Consulta a API externa (OpenFoodFacts) si no existe localmente
- Alta y edición manual de productos
- Control de stock y precios

### Modo Venta

- Escaneo continuo de productos
- Acumulación automática de productos iguales
- Cálculo de total en tiempo real
- Registro de ventas en base de datos

## Estructura de Base de Datos

### Tabla: products
- id (SERIAL PRIMARY KEY)
- barcode (VARCHAR UNIQUE)
- name (VARCHAR)
- description (TEXT NULLABLE)
- price (DECIMAL)
- stock (INTEGER)
- timestamps

### Tabla: sales
- id (SERIAL PRIMARY KEY)
- total (DECIMAL)
- created_at (TIMESTAMP)

### Tabla: sale_items
- id (SERIAL PRIMARY KEY)
- sale_id (FK -> sales.id)
- product_id (FK -> products.id)
- quantity (INTEGER)
- price (DECIMAL)

## Rutas Principales

- `/` - Página principal / Modo venta
- `/products` - Listado de productos
- `/products/create` - Alta de producto
- `/products/{id}/edit` - Editar producto
- `/barcode/scan` - Vista de escaneo
- `/sales/complete` - Finalizar venta

## API Externa

La aplicación consulta OpenFoodFacts API cuando un producto no existe localmente:
- URL: https://world.openfoodfacts.org/api/v0/product/{barcode}.json
- Autocompletado de nombre y descripción cuando hay datos disponibles

## Tecnologías

- **Backend**: PHP 8.2 + Laravel 11
- **Base de Datos**: PostgreSQL
- **Frontend**: Blade Templates + JavaScript vanilla
- **CSS**: Tailwind CSS
- **Librerías JS**: QuaggaJS (lectura de código de barras)

## Desarrollo

Estructura del proyecto:

```
app/
├── Http/Controllers/
│   ├── ProductController.php
│   ├── SaleController.php
│   └── BarcodeController.php
├── Models/
│   ├── Product.php
│   ├── Sale.php
│   └── SaleItem.php
└── Services/
    └── ProductApiService.php

database/migrations/
├── xxxx_create_products_table.php
├── xxxx_create_sales_table.php
└── xxxx_create_sale_items_table.php

resources/views/
├── layouts/
│   └── app.blade.php
├── products/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── sales/
│   ├── pos.blade.php
│   └── receipt.blade.php
└── barcode/
    └── scan.blade.php

public/js/
└── barcode-scanner.js
```

## Licencia

Proyecto educativo - Uso libre
