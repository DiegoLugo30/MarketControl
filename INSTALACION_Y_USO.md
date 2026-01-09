# Guía de Instalación y Uso - Sistema POS Barcode

## Instalación Paso a Paso

### 1. Requisitos Previos

Asegúrate de tener instalado:
- PHP 8.2 o superior
- Composer (https://getcomposer.org)
- PostgreSQL 12 o superior
- Extensiones PHP: pdo_pgsql, mbstring, openssl, tokenizer, xml

### 2. Crear Base de Datos PostgreSQL

Abre la terminal de PostgreSQL (psql) y ejecuta:

```sql
CREATE DATABASE pos_barcode;
CREATE USER pos_user WITH PASSWORD 'tu_password_seguro';
GRANT ALL PRIVILEGES ON DATABASE pos_barcode TO pos_user;

-- Si usas PostgreSQL 15 o superior, también necesitas:
\c pos_barcode
GRANT ALL ON SCHEMA public TO pos_user;
```

### 3. Instalar Dependencias de Laravel

En la raíz del proyecto, ejecuta:

```bash
composer install
```

Esto instalará todas las dependencias de Laravel incluyendo:
- Laravel Framework 11.x
- Guzzle HTTP (para consultas a APIs externas)
- Y todas las dependencias necesarias

### 4. Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar el archivo .env y configurar la base de datos
```

Edita `.env` con tus datos:

```env
APP_NAME="POS Barcode"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_barcode
DB_USERNAME=pos_user
DB_PASSWORD=tu_password_seguro
```

### 5. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará las siguientes tablas:
- `products` - Productos con código de barras
- `sales` - Registro de ventas
- `sale_items` - Detalles de cada venta

### 7. Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: **http://localhost:8000**

## Uso del Sistema

### Pantalla Principal - Punto de Venta (POS)

**URL**: http://localhost:8000

La pantalla principal es el punto de venta donde puedes:

1. **Escanear productos** usando:
   - Lector USB de código de barras (conecta y escanea)
   - Teclado (ingresa el código manualmente y presiona ENTER)

2. **Gestionar el carrito**:
   - Los productos escaneados se agregan automáticamente
   - Productos iguales se acumulan en cantidad
   - Puedes modificar cantidades con los botones + y -
   - Eliminar productos con el botón X

3. **Finalizar venta**:
   - Click en "Finalizar Compra"
   - El sistema registra la venta
   - Descuenta automáticamente el stock
   - Muestra el recibo

### Escaneo de Código de Barras

**URL**: http://localhost:8000/barcode/scan

Dos modos de escaneo:

#### Modo 1: Lector USB
- Conecta tu lector de código de barras USB
- El campo de texto tiene autofocus
- Escanea el código (el lector enviará ENTER automáticamente)
- El sistema busca el producto

#### Modo 2: Cámara del Dispositivo
- Click en "Cámara"
- Permite acceso a la cámara
- Apunta al código de barras
- QuaggaJS detecta automáticamente los códigos EAN, UPC, Code 128, etc.
- Vibración al detectar código (en móviles)

**Flujo de búsqueda**:
1. Busca en base de datos local
2. Si no existe → Consulta OpenFoodFacts API
3. Si la API tiene datos → Formulario pre-llenado para completar precio/stock
4. Si no hay datos → Formulario manual completo

### Gestión de Productos

**URL**: http://localhost:8000/products

- **Listar**: Ver todos los productos con stock y precios
- **Crear**: Agregar productos manualmente
- **Editar**: Modificar precio, stock, nombre, descripción
- **Eliminar**: Solo si no tiene ventas asociadas

Campos del producto:
- Código de barras (único)
- Nombre
- Descripción (opcional)
- Precio
- Stock

### Historial de Ventas

**URL**: http://localhost:8000/sales

- Lista todas las ventas realizadas
- Muestra: fecha, hora, cantidad de items, total
- Click en "Ver Recibo" para ver el detalle
- Impresión de recibos disponible

### API Externa - OpenFoodFacts

El sistema consulta automáticamente la API de OpenFoodFacts cuando un código no existe localmente:

- **API**: https://world.openfoodfacts.org
- **Datos obtenidos**: Nombre del producto, marca, cantidad, categorías
- **Ventaja**: Pre-llena información para productos comerciales
- **Requiere**: Conexión a internet

## Estructura de Archivos Principales

```
├── app/
│   ├── Http/Controllers/
│   │   ├── ProductController.php    # CRUD de productos
│   │   ├── SaleController.php       # Gestión de ventas
│   │   └── BarcodeController.php    # Escaneo y búsqueda
│   ├── Models/
│   │   ├── Product.php              # Modelo de producto
│   │   ├── Sale.php                 # Modelo de venta
│   │   └── SaleItem.php             # Modelo de item de venta
│   └── Services/
│       └── ProductApiService.php    # Integración con OpenFoodFacts
├── database/migrations/             # Migraciones de base de datos
├── resources/views/
│   ├── layouts/app.blade.php        # Layout principal
│   ├── barcode/scan.blade.php       # Vista de escaneo
│   ├── products/                    # Vistas de productos
│   └── sales/                       # Vistas de ventas
├── routes/web.php                   # Rutas de la aplicación
└── public/                          # Archivos públicos
```

## Características Técnicas

### Base de Datos

- **Motor**: PostgreSQL
- **ORM**: Eloquent
- **Relaciones**:
  - Product hasMany SaleItem
  - Sale hasMany SaleItem
  - SaleItem belongsTo Product y Sale

### Frontend

- **Motor de plantillas**: Blade
- **CSS**: Tailwind CSS (CDN)
- **JavaScript**: jQuery + Vanilla JS
- **Librería de escaneo**: QuaggaJS (lectura de códigos de barras con cámara)
- **Iconos**: Font Awesome

### Seguridad

- Validaciones de backend en todos los formularios
- CSRF protection en formularios
- Sanitización de inputs
- Control de stock antes de finalizar venta
- Transacciones de base de datos en ventas

### API Externa

- Timeout de 5 segundos en consultas
- Manejo de errores y fallbacks
- Logs de errores en consultas API

## Solución de Problemas

### Error de conexión a PostgreSQL

```
SQLSTATE[08006] Connection refused
```

**Solución**:
1. Verifica que PostgreSQL esté corriendo
2. Confirma usuario y contraseña en `.env`
3. Verifica el puerto (por defecto 5432)

### Error de permisos en PostgreSQL 15+

```
SQLSTATE[42501] Permission denied for schema public
```

**Solución**:
```sql
GRANT ALL ON SCHEMA public TO pos_user;
```

### La cámara no funciona

**Causas comunes**:
1. Navegador sin permisos (debe ser HTTPS en producción)
2. Navegador no soporta getUserMedia
3. Cámara en uso por otra aplicación

**Solución**:
- Usa Chrome/Firefox actualizados
- En desarrollo, localhost funciona sin HTTPS
- Cierra otras apps que usen la cámara

### Código de barras no se detecta con cámara

**Consejos**:
1. Buena iluminación
2. Código de barras limpio y sin arrugas
3. Distancia apropiada (15-30 cm)
4. Mantener estable 1-2 segundos

## Extensiones Futuras

Ideas para mejorar el sistema:

1. **Reportes**: Dashboard con estadísticas de ventas
2. **Usuarios**: Sistema de login con roles (cajero, administrador)
3. **Categorías**: Organizar productos por categorías
4. **Proveedores**: Gestión de proveedores
5. **Alertas de stock**: Notificaciones cuando el stock es bajo
6. **Impresión de etiquetas**: Generar códigos de barras para productos sin código
7. **Múltiples métodos de pago**: Efectivo, tarjeta, transferencia
8. **Devoluciones**: Sistema de devoluciones y notas de crédito
9. **Descuentos y promociones**: Sistema de descuentos
10. **API REST**: Endpoints para integración con otros sistemas

## Soporte

Para reportar errores o solicitar funcionalidades:
- Crear un issue en el repositorio
- Documentar el problema con capturas de pantalla
- Incluir versión de PHP, PostgreSQL y navegador

## Licencia

Proyecto educativo - Uso libre
