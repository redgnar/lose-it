DOCKER_COMPOSE = docker compose
PHP_CONT = php
EXEC_PHP = $(DOCKER_COMPOSE) exec $(PHP_CONT)

.PHONY: setup cleanup cln up down tt tc tu ta ti tf tff lm l qa cf phpstan migrations-diff migrations-migrate migrations-down lf ia sc cp doc ug

# Setup & Cleanup
setup:
	$(DOCKER_COMPOSE) build --pull --no-cache
	$(MAKE) up
	$(MAKE) cp c="install"
	$(EXEC_PHP) bash -c "cd ../tools && composer install"
	$(MAKE) lm

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
	$(EXEC_PHP) vendor/bin/phpunit

tc:
	$(EXEC_PHP) vendor/bin/phpunit --coverage-clover coverage.xml --coverage-html coverage

tu:
	$(EXEC_PHP) vendor/bin/phpunit --testsuite Unit

ta:
	$(EXEC_PHP) vendor/bin/phpunit --testsuite Api

ti:
	$(EXEC_PHP) vendor/bin/phpunit --testsuite Integration

tf:
	$(EXEC_PHP) vendor/bin/phpunit --testsuite Functional

tff:
	$(EXEC_PHP) vendor/bin/phpunit --stop-on-failure

lm:
	$(EXEC_PHP) php bin/console hautelook:fixtures:load -n || true

l:
	$(DOCKER_COMPOSE) logs -f $(c)

# Code Quality
qa: cf phpstan tt

cf:
	$(EXEC_PHP) ../tools/vendor/bin/php-cs-fixer fix

phpstan:
	$(EXEC_PHP) ../tools/vendor/bin/phpstan analyze src tests

quality-all: qa

# Database
migrations-diff:
	$(EXEC_PHP) php bin/console doctrine:migrations:diff

migrations-migrate:
	$(EXEC_PHP) php bin/console doctrine:migrations:migrate -n

migrations-down:
	$(EXEC_PHP) php bin/console doctrine:migrations:execute --down

lf:
	$(EXEC_PHP) php bin/console doctrine:fixtures:load -n

# Development Tools
ia:
	$(DOCKER_COMPOSE) exec $(PHP_CONT) /bin/bash

sc:
	$(EXEC_PHP) php bin/console $(c)

cp:
	$(EXEC_PHP) composer $(c)

doc:
	$(EXEC_PHP) php bin/console nelmio:apidoc:dump

# Additional Shortcuts
dc:
	$(DOCKER_COMPOSE) $(c)

ug: cln setup
