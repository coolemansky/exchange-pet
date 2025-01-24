# Variables
DOCKER_COMPOSE = docker-compose
EXEC_PHP = $(DOCKER_COMPOSE) exec php
COMPOSER = $(DOCKER_COMPOSE) exec php composer
SYMFONY = $(EXEC_PHP) php bin/console

# =========
# Shortcuts
# =========
up: docker-up ## Launch application
up-ui: docker-up-ui ## Launch application with UI
up-lite: docker-up-lite ## Launch application (only basic containers)
down: docker-down ## Stop application
restart: down up ## Restart application (only basic containers)
restart-ui: down up-ui ## Restart application with UI
restart-lite: down up-lite ## Restart application
check: lint-yaml lint # Analyze ## Run linters and analyzers
fix: lint-fix # Analyze-fix ## Run quick automatic fixes

init: docker-down docker-build composer-install git-init-hooks sf-assets-install db-recreate ## Install application
update: docker-up sf-assets-install git-init-hooks db-recreate ## Update application (composer update removed for security reasons)

git-init-hooks:
	git config core.hooksPath githooks
	chmod +x githooks/pre-commit

# ======
# Docker
# ======
docker-up: ## Docker up
	$(DOCKER_COMPOSE) --profile extended up -d --force-recreate

docker-up-ui: ## Docker up (with ui container)
	$(DOCKER_COMPOSE) --profile ui up -d --force-recreate

docker-up-lite: ## Docker up (only basic containers)
	$(DOCKER_COMPOSE) up -d

docker-down: ## Docker down
	$(DOCKER_COMPOSE) --profile extended down --remove-orphans

docker-down-clear: ## Docker down with volumes delete
	$(DOCKER_COMPOSE) down -v --remove-orphans

docker-pull: ## Docker pull
	$(DOCKER_COMPOSE) pull

docker-build: ## Docker build
	$(DOCKER_COMPOSE) down --remove-orphans
#	rm -r -f 'docker/_data/postgres_data'
#	rm -r -f 'docker/_data/redis'
#	rm -r -f 'docker/_data/nginx'
#	mkdir "docker/_data/postgres_data"
#	mkdir "docker/_data/redis"
#	mkdir "docker/_data/nginx"
	$(DOCKER_COMPOSE) --profile extended up -d --build

# ========
# Composer
# ========
composer-install: composer-install-simple sf-cache-clear-bp ## Composer install

composer-install-simple:
	$(COMPOSER) install

#composer-update: composer-update-simple sf-cache-clear-bp ## Composer update

#composer-update-simple:
#	$(COMPOSER) update

composer-require: ## Composer require. Use FLAGS="vendor/package --flags"
	$(COMPOSER) require $(FLAGS)

composer-clear-cache: ## Composer require. Use FLAGS="vendor/package --flags"
	$(COMPOSER) clearcache

# =======
# Symfony
# =======
sf-fixtures-load: ## Symfony run fixtures
	$(SYMFONY) doctrine:fixtures:load --no-debug

sf-fixtures-load-append: ## Symfony run fixtures
	$(SYMFONY) doctrine:fixtures:load --no-interaction --append --no-debug

sf-fixtures-load-append-test: ## Symfony run fixtures fot test env
	$(SYMFONY) doctrine:fixtures:load --env=test --no-interaction --append --no-debug

sf-migrations: ## Symfony run migrations
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --no-debug

sf-migrations-test: ## Symfony run migrations for test env
	$(SYMFONY) doctrine:migrations:migrate --env=test --no-interaction --no-debug

sf-migrations-diff:
	$(SYMFONY) doctrine:migrations:diff --no-debug

sf-cache-clear-bp: sf-cache-clear-dir sf-cache-clear

sf-cache-clear-dir:
	rm -r -f 'app/var/cache'
	rm -r -f '/tmp/cache/'

#sf-cache-clear: ## Symfony cache clear (Repeats on fail)
#	until $(SYMFONY) cache:clear; do :; done

sf-cache-clear: ## Symfony cache clear (Repeats on fail)
	$(SYMFONY) cache:clear
	$(SYMFONY) cache:pool:prune

sf-cache-clear-test: ## Symfony cache clear for test env
	rm -r -f 'app/var/cache/test'
	$(SYMFONY) cache:pool:prune

sf-assets-install: ## Symfony assets install
	$(SYMFONY) assets:install

sf-kafka-fixtures-load: ## Symfony fixtures for kafka
	$(SYMFONY) app:kafka:fixtures:load Contractor1c
	$(SYMFONY) app:kafka:fixtures:load Contract1c
	$(SYMFONY) app:kafka:fixtures:load ExtraService
	$(SYMFONY) app:kafka:fixtures:load Packaging
	$(SYMFONY) app:kafka:fixtures:load Service
	$(SYMFONY) app:kafka:fixtures:load User

sf-kafka-get: ## Symfony assets install
	$(SYMFONY) process_manager:run

sf-kafka-get-contract: ## Symfony assets install
	$(SYMFONY) process_manager:run --process_alias=contract

sf-messenger-restart: sf-messenger-stop sf-messenger-start ## Restart messenger consumers

sf-messenger-start:
	$(SYMFONY) messenger:consume async -vv

sf-messenger-stop:
	$(SYMFONY) messenger:stop-workers

# ========
# Database
# ========
.PHONY: db-recreate
db-recreate: sf-cache-clear db-schema-drop sf-migrations sf-fixtures-load-append ## Recreate database and load fixtures

.PHONY: db-recreate-test
db-recreate-test: db-drop-test db-create-test sf-migrations-test ## Recreate test database

.PHONY: db-test-init
db-test-init: db-create-test sf-migrations-test sf-fixtures-load-append-test ## Init test database

.PHONY: db-recreate-clean
db-recreate-clean: db-schema-drop sf-migrations ## Recreate database

.PHONY: db-schema-drop
db-schema-drop: ## Drop database schema
	$(SYMFONY) doctrine:schema:drop --full-database --force

.PHONY: db-drop-test
db-drop-test:
	$(SYMFONY) --env=test doctrine:database:drop --if-exists --force

.PHONY: db-create-test
db-create-test:
	$(SYMFONY) --env=test doctrine:database:create

.PHONY: db-schema-drop-test
db-schema-drop-test:
	$(SYMFONY) --env=test doctrine:schema:drop --full-database --force

.PHONY: db-schema-create-test
db-schema-create-test:
	$(SYMFONY) --env=test doctrine:schema:create

# =================
# Linters/Analysers
# =================
analyze: ## Print Psalm analyzes
	$(EXEC_PHP) vendor/bin/psalm -- --find-dead-code --find-unused-psalm-suppress --long-progress --no-diff --threads=4

# https://psalm.dev/docs/manipulating_code/fixing/
analyze-fix: ## Fix simple Psalm errors (modifies files)
	$(EXEC_PHP) vendor/bin/psalm --alter --issues=MismatchingDocblockReturnType,MissingReturnType --threads=4

lint: ## Print php-cs-fixer diff
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

lint-fix: ## Fix php-cs-fixer errors (modifies files)
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix

lint-yaml: ## Symfony YAML linter (https://symfony.com/doc/current/components/yaml.html#syntax-validation)
	$(SYMFONY) lint:yaml config --parse-tags

lint-container: ## Symfony Container linter
	$(SYMFONY) lint:container

# =====
# Tests
# =====
.PHONY: test
test: sf-cache-clear-test db-recreate-test test-all

.PHONY: test-coverage
test-coverage: ## Run tests with text coverage
	$(EXEC_PHP) sh -c 'XDEBUG_MODE=off ./vendor/bin/paratest -p1 --runner=WrapperRunner --coverage-text --passthru-php="-d pcov.enabled=1 -d pcov.directory=src" tests'

.PHONY: test-coverage-report
test-coverage-report: ## Run tests with html coverage report
	$(EXEC_PHP) sh -c 'XDEBUG_MODE=off ./vendor/bin/paratest -p1 --runner=WrapperRunner --coverage-html=/var/www/pet-project/var/coverage --passthru-php="-d pcov.enabled=1 -d pcov.directory=src" tests'

.PHONY: test-all
test-all: ## Run all tests
	$(EXEC_PHP) ./vendor/bin/paratest -p1 --runner=WrapperRunner

.PHONY: test-unit
test-unit: ## Run tests from "unit" group
	$(EXEC_PHP) ./vendor/bin/paratest -p4 --runner=WrapperRunner tests/Unit

.PHONY: test-integration
test-integration: ## Run tests from "integration" group
	$(EXEC_PHP) ./vendor/bin/paratest -p1 --runner=WrapperRunner tests/Integration

.PHONY: test-functional
test-functional: ## Run tests from "functional" group
	$(EXEC_PHP) ./vendor/bin/paratest -p1 --runner=WrapperRunner tests/Functional


# =======
# Helpers
# =======

help: ## Show this help
	@egrep -h '\s##\s' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: help

.DEFAULT_GOAL:= help
