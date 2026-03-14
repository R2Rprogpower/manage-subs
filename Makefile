SHELL := /bin/sh

.PHONY: install-hooks check fmt lint test up build

install-hooks:
	@mkdir -p .git/hooks
	@cp scripts/pre-commit .git/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit
	@echo "Installed .git/hooks/pre-commit"

up:
	docker compose up -d

build:
	docker compose up -d --build

fmt:
	docker compose exec -T app ./vendor/bin/pint

lint:
	docker compose exec -T -u root app rm -rf /tmp/phpstan
	docker compose exec -T app ./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=1G

test:
	docker compose exec -T app php artisan test --testsuite=Unit

check: fmt lint test
	@echo "All checks passed."

.PHONY: ci-up ci-down ci-setup ci-check

ci-up:
	docker compose -f docker-compose.yml -f docker-compose.ci.yml up -d --build

ci-down:
	docker compose -f docker-compose.yml -f docker-compose.ci.yml down -v

ci-setup:
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app git config --global --add safe.directory /var/www/html
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app composer install --no-interaction --no-progress --prefer-dist
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app cp .env.example .env
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app php artisan key:generate
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app php artisan migrate --force

ci-check:
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app ./vendor/bin/pint
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app rm -rf /tmp/phpstan
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app ./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=1G
	docker compose -f docker-compose.yml -f docker-compose.ci.yml exec -T -u root app php artisan test --testsuite=Unit
	@echo "All CI checks passed."
