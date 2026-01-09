# 🚀 Guía de Inicio Rápido - POS Barcode

## ¿Primera vez usando este proyecto? Lee esto primero

Tienes **dos opciones** para ejecutar la aplicación:

---

## ✅ Opción 1: Docker (MÁS FÁCIL) 🐳

**Ventajas:**
- ✅ No instalas nada (solo Docker)
- ✅ Funciona en 2 minutos
- ✅ Todo configurado automáticamente
- ✅ Mismo entorno para todos

### Paso 1: Instalar Docker Desktop

**Descarga e instala Docker Desktop:**
- Windows/Mac: https://www.docker.com/products/docker-desktop
- Tiempo: 5 minutos

### Paso 2: Ejecutar la Aplicación

#### En Windows:
```bash
# Opción A: Doble clic en el archivo:
start-docker.bat

# Opción B: Desde CMD/PowerShell en la carpeta del proyecto:
docker-compose up -d --build
```

#### En Linux/Mac:
```bash
# En la terminal, dentro de la carpeta del proyecto:
docker-compose up -d --build
```

### Paso 3: Abrir el Navegador

Abre: **http://localhost:8000**

¡Listo! La aplicación está corriendo 🎉

### Comandos Útiles

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Detener aplicación
docker-compose down

# Reiniciar aplicación
docker-compose restart

# Ver documentación completa
# Lee: README_DOCKER.md
```

---

## 📦 Opción 2: Instalación Manual

**Ventajas:**
- ✅ Control total del entorno
- ✅ Útil si ya tienes PHP/PostgreSQL instalado

**Desventajas:**
- ⏱️ Más pasos de configuración
- 🔧 Requiere instalar y configurar múltiples herramientas

### Requisitos

Debes instalar:
1. **PHP 8.2+**: https://windows.php.net/download/
2. **Composer**: https://getcomposer.org/download/
3. **PostgreSQL 12+**: https://www.postgresql.org/download/

### Pasos de Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Crear base de datos PostgreSQL
# Abre pgAdmin o psql y ejecuta:
CREATE DATABASE pos_barcode;
CREATE USER pos_user WITH PASSWORD 'tu_password';
GRANT ALL PRIVILEGES ON DATABASE pos_barcode TO pos_user;

# 3. Configurar .env
copy .env.example .env
# Edita .env y configura los datos de PostgreSQL

# 4. Generar clave
php artisan key:generate

# 5. Ejecutar migraciones
php artisan migrate

# 6. Iniciar servidor
php artisan serve
```

Abre: **http://localhost:8000**

Ver guía completa: **INSTALACION_Y_USO.md**

---

## 🆘 ¿Problemas?

### Docker no inicia

1. Verifica que Docker Desktop esté corriendo
2. Reinicia Docker Desktop
3. Ejecuta: `docker-compose down` y luego `docker-compose up -d`

### Puerto 8000 ocupado

**Con Docker:**
Edita `docker-compose.yml` y cambia:
```yaml
nginx:
  ports:
    - "8080:80"  # Cambiar 8000 por 8080
```

**Sin Docker:**
```bash
php artisan serve --port=8080
```

### Error de base de datos

**Con Docker:** Los errores de BD son raros, todo está configurado automáticamente

**Sin Docker:** Verifica que:
1. PostgreSQL esté corriendo
2. Usuario y contraseña en `.env` sean correctos
3. La base de datos exista

---

## 📚 Documentación Completa

- **README.md** - Descripción general del proyecto
- **README_DOCKER.md** - Todo sobre Docker (comandos, troubleshooting)
- **INSTALACION_Y_USO.md** - Instalación manual detallada
- **COMANDOS_UTILES.md** - Referencia de comandos Laravel, PostgreSQL, etc.

---

## ✨ Primeros Pasos en la Aplicación

Una vez que la aplicación esté corriendo:

### 1. Crear tu primer producto

- Click en **"Productos"** en el menú
- Click en **"Nuevo Producto"**
- Llena los datos:
  - Código de barras: `123456789` (puedes inventarlo)
  - Nombre: `Producto de Prueba`
  - Precio: `10.50`
  - Stock: `100`
- Guarda

### 2. Hacer una venta

- Vuelve a **"Punto de Venta"** (menú principal)
- Escribe el código: `123456789` y presiona ENTER
- El producto se agrega al carrito
- Click en **"Finalizar Compra"**
- ¡Venta registrada! 🎉

### 3. Probar el escáner de códigos

- Click en **"Escanear"**
- Prueba el modo **USB** (con teclado o lector USB)
- Prueba el modo **Cámara** (si tienes un código de barras físico)

---

## 🎯 ¿Qué Opción Elegir?

### Usa Docker si:
- ✅ Quieres la configuración más rápida
- ✅ No tienes PHP/PostgreSQL instalado
- ✅ Quieres evitar problemas de compatibilidad
- ✅ Vas a trabajar en equipo (mismo entorno para todos)

### Usa Instalación Manual si:
- ✅ Ya tienes PHP y PostgreSQL configurados
- ✅ Prefieres control total del entorno
- ✅ Quieres aprender más sobre la configuración

---

## 💡 Recomendación

**Si es tu primera vez con el proyecto → USA DOCKER** 🐳

Es más rápido, más simple, y no vas a tener problemas de configuración.

---

## 🎊 ¡Éxito!

Si llegaste hasta aquí y la aplicación está corriendo, ¡felicitaciones!

Ahora explora las funcionalidades:
- Gestión de productos
- Punto de venta
- Historial de ventas
- Escaneo con cámara

**¿Preguntas?** Revisa la documentación en los archivos README.
