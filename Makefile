.PHONY: help up down restart build logs shell \
       migrate migrate-fresh seed \
       test test-unit test-feature \
       key-generate optimize clear-cache \
       ps db-shell tinker sync-vendor

# Config
COMPOSE = docker compose
EXEC = $(COMPOSE) exec -T app
EXEC_IT = $(COMPOSE) exec app

#------------------------------------------------------------------------------
# Help
#------------------------------------------------------------------------------

help: ## Show this help
	@echo ""
	@echo "DQ Tahfiz API - Local Development"
	@echo "=================================="
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Docker:"
	@echo "  up              Start containers (detached) and sync vendor to host"
	@echo "  down            Stop and remove containers"
	@echo "  restart         Restart containers"
	@echo "  build           Rebuild containers"
	@echo "  ps              Show running containers"
	@echo "  logs            Tail app logs"
	@echo "  sync-vendor     Copy vendor from container to host (for IDE support)"
	@echo ""
	@echo "Laravel:"
	@echo "  shell           Open shell in app container"
	@echo "  tinker          Open Laravel tinker REPL"
	@echo "  key-generate    Generate application key"
	@echo "  optimize        Cache config, routes, views"
	@echo "  clear-cache     Clear all Laravel caches"
	@echo ""
	@echo "Database:"
	@echo "  migrate         Run migrations"
	@echo "  migrate-fresh   Drop all tables and re-migrate"
	@echo "  seed            Run database seeders"
	@echo "  db-shell        Open mysql shell in DB container"
	@echo ""
	@echo "Testing:"
	@echo "  test            Run PHPUnit tests"
	@echo "  test-unit       Run unit tests only"
	@echo "  test-feature    Run feature tests only"
	@echo ""

#------------------------------------------------------------------------------
# Docker
#------------------------------------------------------------------------------

up: ## Start containers and sync vendor to host
	$(COMPOSE) up -d --build --wait
	@$(MAKE) sync-vendor

down: ## Stop containers
	$(COMPOSE) down -v

restart: down up ## Restart containers

build: ## Rebuild containers from scratch
	$(COMPOSE) build --no-cache
	$(COMPOSE) up -d --wait
	@$(MAKE) sync-vendor

ps: ## Show container status
	$(COMPOSE) ps

sync-vendor: ## Copy vendor from container to host for IDE support
	@echo "Syncing vendor from container to host..."
	@$(COMPOSE) exec -T app tar cf - -C /var/www/html vendor | tar xf - -C .
	@echo "Vendor synced successfully."

logs: ## Tail app logs
	$(COMPOSE) logs -f app

#------------------------------------------------------------------------------
# Laravel
#------------------------------------------------------------------------------

shell: ## Open shell in app container
	$(EXEC_IT) sh

tinker: ## Open tinker REPL
	$(EXEC_IT) php artisan tinker

key-generate: ## Generate app key
	$(EXEC) php artisan key:generate

optimize: ## Cache config, routes, views
	$(EXEC) php artisan config:cache
	$(EXEC) php artisan route:cache
	$(EXEC) php artisan view:cache

clear-cache: ## Clear all caches
	$(EXEC) php artisan config:clear
	$(EXEC) php artisan route:clear
	$(EXEC) php artisan view:clear
	$(EXEC) php artisan cache:clear

#------------------------------------------------------------------------------
# Database
#------------------------------------------------------------------------------

migrate: ## Run migrations
	$(EXEC) php artisan migrate --force

migrate-fresh: ## Drop all tables and re-migrate
	$(EXEC) php artisan migrate:fresh --force

seed: ## Run database seeders
	$(EXEC) php artisan db:seed --force

db-shell: ## Open mysql shell
	$(COMPOSE) exec db mysql -uroot -psecret dq_tahfiz

#------------------------------------------------------------------------------
# Testing
#------------------------------------------------------------------------------

test: ## Run PHPUnit tests
	$(EXEC) php artisan test

test-unit: ## Run unit tests only
	$(EXEC) php artisan test --testsuite=Unit

test-feature: ## Run feature tests only
	$(EXEC) php artisan test --testsuite=Feature
