# Variables
DOCKER_COMPOSE = docker-compose
EXEC_PHP = $(DOCKER_COMPOSE) exec php
COMPOSER = $(DOCKER_COMPOSE) exec php composer
SYMFONY = $(EXEC_PHP) php bin/console

# =========
# Shortcuts
# =========
up: docker-up ## Launch application
down: docker-down ## Stop application
restart: down up ## Restart application (only basic containers)

init: docker-down docker-build composer-install ## Install application
update: docker-up ## Update application (composer update removed for security reasons)

git-init-hooks:
	git config core.hooksPath githooks
	chmod +x githooks/pre-commit

# ======
# Docker
# ======
docker-up: ## Docker up
	$(DOCKER_COMPOSE) --profile extended up -d --force-recreate

docker-down: ## Docker down
	$(DOCKER_COMPOSE) --profile extended down --remove-orphans

docker-pull: ## Docker pull
	$(DOCKER_COMPOSE) pull

docker-build: ## Docker build
	$(DOCKER_COMPOSE) down --remove-orphans
	$(DOCKER_COMPOSE) --profile extended up -d --build

# ========
# Composer
# ========
composer-install: composer-install-simple## Composer install

composer-install-simple:
	$(COMPOSER) install

composer-require: ## Composer require. Use FLAGS="vendor/package --flags"
	$(COMPOSER) require $(FLAGS)

composer-clear-cache: ## Composer require. Use FLAGS="vendor/package --flags"
	$(COMPOSER) clearcache

# =======
# Symfony
# =======
sf-cache-clear: ## Symfony cache clear (Repeats on fail)
	$(SYMFONY) cache:clear
	$(SYMFONY) cache:pool:prune
