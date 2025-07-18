.PHONY: help build up down restart logs shell composer artisan migrate seed fresh test

# Configurações
DOCKER_COMPOSE = docker-compose
DOCKER_EXEC = docker exec -it vox-kanban-app

help: ## Mostra esta ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Faz build da imagem base
	@echo "🔨 Fazendo build da imagem vox-kanban-app..."
	docker build --build-arg user=www --build-arg uid=1000 -t vox-kanban-app:latest .

rebuild: down build up ## Rebuild completo da aplicação

up: ## Inicia todos os serviços
	$(DOCKER_COMPOSE) up -d

up-dev: ## Inicia todos os serviços incluindo Node.js para desenvolvimento
	$(DOCKER_COMPOSE) --profile dev up -d

down: ## Para todos os serviços
	$(DOCKER_COMPOSE) down

restart: down up ## Reinicia todos os serviços

logs: ## Mostra logs de todos os serviços
	$(DOCKER_COMPOSE) logs -f

logs-app: ## Mostra logs apenas da aplicação
	$(DOCKER_COMPOSE) logs -f app

logs-queue: ## Mostra logs da queue
	$(DOCKER_COMPOSE) logs -f queue

logs-nginx: ## Mostra logs do Nginx
	$(DOCKER_COMPOSE) logs -f webserver

shell: ## Acessa o shell da aplicação
	$(DOCKER_EXEC) bash

shell-root: ## Acessa o shell como root
	docker exec -it --user root vox-kanban-app bash

composer: ## Executa comando do Composer (ex: make composer ARGS="install")
	$(DOCKER_EXEC) composer $(ARGS)

artisan: ## Executa comando do Artisan (ex: make artisan ARGS="migrate")
	$(DOCKER_EXEC) php artisan $(ARGS)

migrate: ## Executa as migrações
	$(DOCKER_EXEC) php artisan migrate

migrate-fresh: ## Executa migrate:fresh
	$(DOCKER_EXEC) php artisan migrate:fresh

seed: ## Executa os seeders
	$(DOCKER_EXEC) php artisan db:seed

fresh: ## Executa migrate:fresh + seed
	$(DOCKER_EXEC) php artisan migrate:fresh --seed

test: ## Executa os testes
	$(DOCKER_EXEC) php artisan test

install: ## Primeira instalação completa
	@echo "🚀 Iniciando instalação completa do Vox Kanban..."
	cp .env.docker .env
	@echo "🔨 Fazendo build da imagem base..."
	docker build --build-arg user=www --build-arg uid=1000 -t vox-kanban-app:latest .
	@echo "🚢 Subindo todos os serviços..."
	$(DOCKER_COMPOSE) up -d
	@echo "⏳ Aguardando serviços ficarem prontos..."
	sleep 15
	$(DOCKER_EXEC) composer install --optimize-autoloader
	$(DOCKER_EXEC) php artisan key:generate
	$(DOCKER_EXEC) php artisan migrate:fresh --seed
	$(DOCKER_EXEC) php artisan storage:link
	$(DOCKER_EXEC) php artisan config:cache
	$(DOCKER_EXEC) php artisan route:cache
	$(DOCKER_EXEC) php artisan view:cache
	$(MAKE) build-assets
	@echo "✅ Instalação completa concluída!"
	@echo "📱 Aplicação: http://localhost:8000"
	@echo "📧 MailHog: http://localhost:8025"
	@echo "🗄️  PostgreSQL: localhost:5432"
	@echo "🔴 Redis: localhost:6379"
	@echo "⚡ Reverb WebSocket: ws://localhost:8080"

setup-dev: ## Configuração para desenvolvimento
	@echo "🔧 Configurando ambiente de desenvolvimento..."
	cp .env.docker .env
	@echo "🔨 Fazendo build da imagem base..."
	docker build --build-arg user=www --build-arg uid=1000 -t vox-kanban-app:latest .
	@echo "🚢 Subindo todos os serviços..."
	$(DOCKER_COMPOSE) --profile dev up -d
	@echo "⏳ Aguardando serviços ficarem prontos..."
	sleep 15
	$(DOCKER_EXEC) composer install
	$(DOCKER_EXEC) php artisan key:generate
	$(DOCKER_EXEC) php artisan migrate:fresh --seed
	$(DOCKER_EXEC) php artisan storage:link
	@echo "✅ Ambiente de desenvolvimento configurado!"
	@echo "📱 Aplicação: http://localhost:8000"
	@echo "📧 MailHog: http://localhost:8025"
	@echo "🗄️  PostgreSQL: localhost:5432"
	@echo "🔴 Redis: localhost:6379"
	@echo "⚡ Reverb WebSocket: ws://localhost:8080"

clean: ## Remove todos os containers e volumes
	$(DOCKER_COMPOSE) down -v --remove-orphans
	docker system prune -f

backup-db: ## Faz backup do banco de dados
	docker exec vox-kanban-postgres pg_dump -U vox_user vox_kanban > backup_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restaura backup do banco (ex: make restore-db FILE=backup.sql)
	docker exec -i vox-kanban-postgres psql -U vox_user vox_kanban < $(FILE)

psql: ## Acessa o PostgreSQL
	docker exec -it vox-kanban-postgres psql -U vox_user vox_kanban

redis-cli: ## Acessa o Redis CLI
	docker exec -it vox-kanban-redis redis-cli

permissions: ## Corrige permissões de arquivos
	$(DOCKER_EXEC) chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
	$(DOCKER_EXEC) chmod -R 775 /var/www/storage /var/www/bootstrap/cache

queue-work: ## Inicia worker da queue manualmente
	$(DOCKER_EXEC) php artisan queue:work --verbose --tries=3 --timeout=90

reverb-start: ## Inicia servidor Reverb manualmente
	$(DOCKER_EXEC) php artisan reverb:start --host=0.0.0.0 --port=8080

logs-reverb: ## Mostra logs do Reverb
	$(DOCKER_COMPOSE) logs -f reverb

status: ## Mostra status de todos os serviços
	@echo "📊 Status dos serviços:"
	$(DOCKER_COMPOSE) ps

build-assets: ## Faz build dos assets com Node.js
	$(DOCKER_COMPOSE) run --rm node npm install
	$(DOCKER_COMPOSE) run --rm node npm run build