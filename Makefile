# Makefile para simplificar comandos Docker del POS Barcode

.PHONY: help build up down restart logs clean install migrate fresh shell tinker db-backup db-restore test

# Colores para output
GREEN=\033[0;32m
NC=\033[0m # No Color

help: ## Mostrar este mensaje de ayuda
	@echo "$(GREEN)Comandos disponibles:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Construir los contenedores
	@echo "$(GREEN)Construyendo contenedores...$(NC)"
	docker-compose build

up: ## Levantar los contenedores
	@echo "$(GREEN)Levantando contenedores...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)✅ Aplicación disponible en: http://localhost:8000$(NC)"

down: ## Detener los contenedores
	@echo "$(GREEN)Deteniendo contenedores...$(NC)"
	docker-compose down

restart: ## Reiniciar los contenedores
	@echo "$(GREEN)Reiniciando contenedores...$(NC)"
	docker-compose restart

logs: ## Ver logs de todos los contenedores
	docker-compose logs -f

logs-app: ## Ver logs solo de la aplicación
	docker-compose logs -f app

logs-db: ## Ver logs solo de la base de datos
	docker-compose logs -f db

logs-nginx: ## Ver logs solo de Nginx
	docker-compose logs -f nginx

clean: ## Limpiar contenedores y volúmenes
	@echo "$(GREEN)Limpiando todo (¡esto borrará la base de datos!)...$(NC)"
	docker-compose down -v
	docker system prune -f

install: ## Instalar/actualizar dependencias de Composer
	@echo "$(GREEN)Instalando dependencias...$(NC)"
	docker-compose exec app composer install

migrate: ## Ejecutar migraciones
	@echo "$(GREEN)Ejecutando migraciones...$(NC)"
	docker-compose exec app php artisan migrate

fresh: ## Resetear base de datos y ejecutar migraciones
	@echo "$(GREEN)Reseteando base de datos...$(NC)"
	docker-compose exec app php artisan migrate:fresh

shell: ## Acceder a la terminal del contenedor
	docker-compose exec app bash

tinker: ## Abrir Laravel Tinker
	docker-compose exec app php artisan tinker

db: ## Conectar a PostgreSQL
	docker-compose exec db psql -U pos_user -d pos_barcode

db-backup: ## Crear backup de la base de datos
	@echo "$(GREEN)Creando backup...$(NC)"
	docker-compose exec db pg_dump -U pos_user pos_barcode > backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✅ Backup creado$(NC)"

cache-clear: ## Limpiar toda la cache de Laravel
	@echo "$(GREEN)Limpiando cache...$(NC)"
	docker-compose exec app php artisan optimize:clear

routes: ## Ver todas las rutas
	docker-compose exec app php artisan route:list

ps: ## Ver estado de los contenedores
	docker-compose ps

stats: ## Ver estadísticas de uso de recursos
	docker stats

rebuild: down build up ## Reconstruir completamente
	@echo "$(GREEN)✅ Reconstrucción completa$(NC)"

init: build up migrate ## Inicializar proyecto desde cero
	@echo "$(GREEN)✅ Proyecto inicializado$(NC)"
	@echo "$(GREEN)Accede a: http://localhost:8000$(NC)"
