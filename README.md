# API (Laravel 12)

This repository documentation is split into focused files in `docs/` for easier navigation and maintenance.

## Documentation index

1. [Local setup](docs/01-local-setup.md)
2. [API overview](docs/02-api-overview.md)
3. [Module database structure](docs/03-module-database.md)
4. [OpenAPI and docs](docs/04-openapi.md)
5. [Production deploy (blue-green)](docs/05-production-deploy.md)
6. [Rollback and backups](docs/06-rollback-and-backups.md)
7. [Security and troubleshooting](docs/07-security-and-troubleshooting.md)
8. [Template repo workflow (multi-app)](docs/08-template-repo-workflow.md)

## Quick start

```bash
git clone https://github.com/R2Rprogpower/guzleaks.git .
cp .env.example .env
docker compose up -d --build
docker compose exec -T app composer install
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate
```

For full setup and production operations, use the docs index above.
