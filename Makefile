BASE_DIR ?= $(CURDIR)
PHP_CONTAINER ?= php

DOCKER_COMPOSE_FLAGS =
DOCKER_EXEC_FLAGS = -it

DOCKER_COMPOSE = export BASE_DIR=$(BASE_DIR) && docker compose $(DOCKER_COMPOSE_FLAGS)
DOCKER_EXEC    = docker compose exec $(DOCKER_EXEC_FLAGS) $(PHP_CONTAINER)
DOCKER_CP      = docker compose cp $(PHP_CONTAINER)

.PHONY: setup cleanup cln up down tt tc tu ta ti tf tff lm l qa cf phpstan migrations-diff migrations-migrate migrations-down lf ia sc cp doc ug

# Setup & Cleanup
setup:
	$(DOCKER_COMPOSE) down -v
	$(DOCKER_COMPOSE) build --pull
	$(MAKE) up
	${DOCKER_EXEC} composer install
	${DOCKER_EXEC} composer install --working-dir=../tools
	${DOCKER_EXEC} bin/console cache:warmup
	${DOCKER_EXEC} bin/console cache:warmup --env=test

	${DOCKER_EXEC} bin/console doctrine:database:drop --if-exists --no-interaction --force
	${DOCKER_EXEC} bin/console doctrine:database:create --if-not-exists --no-interaction
	${DOCKER_EXEC} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
#	${DOCKER_EXEC} bin/console doctrine:fixtures:load --no-interaction

	${DOCKER_EXEC} bin/console doctrine:database:drop --if-exists --no-interaction --force --env=test
	${DOCKER_EXEC} bin/console doctrine:database:create --if-not-exists --no-interaction --env=test
	${DOCKER_EXEC} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test

cleanup: cln

cln:
	$(DOCKER_COMPOSE) down -v --remove-orphans
	rm -rf app/vendor tools/vendor app/var

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

# Testing
tt:
	$(DOCKER_EXEC) vendor/bin/phpunit

tc:
	$(DOCKER_EXEC) vendor/bin/phpunit --coverage-clover coverage.xml --coverage-html coverage

tu:
	$(DOCKER_EXEC) vendor/bin/phpunit --testsuite Unit

ta:
	$(DOCKER_EXEC) vendor/bin/phpunit --testsuite Api

ti:
	$(DOCKER_EXEC) vendor/bin/phpunit --testsuite Integration

tf:
	$(DOCKER_EXEC) vendor/bin/phpunit --testsuite Functional

tff:
	$(DOCKER_EXEC) vendor/bin/phpunit --stop-on-failure

l:
	$(DOCKER_COMPOSE) logs -f $(c)

# Code Quality
qa: cf phpstan tt

cf:
	$(DOCKER_EXEC) ../tools/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

phpstan:
	$(DOCKER_EXEC) ../tools/vendor/bin/phpstan analyze --memory-limit=1G

quality-all: qa

# Database
migrations-diff:
	$(DOCKER_EXEC) php bin/console doctrine:migrations:diff

migrations-migrate:
	$(DOCKER_EXEC) php bin/console doctrine:migrations:migrate -n

migrations-down:
	$(DOCKER_EXEC) php bin/console doctrine:migrations:execute --down

lf:
	$(DOCKER_EXEC) php bin/console doctrine:fixtures:load -n

# Development Tools
ia:
	$(DOCKER_COMPOSE) exec $(PHP_CONT) /bin/bash

sc:
	$(DOCKER_EXEC) php bin/console $(c)

cp:
	$(DOCKER_EXEC) composer $(c)

doc:
	$(DOCKER_EXEC) php bin/console nelmio:apidoc:dump

# Additional Shortcuts
dc:
	$(DOCKER_COMPOSE) $(c)

ug: cln setup
