# Local Setup

## Requirements

- Docker + Docker Compose
- GNU Make (optional, for helper commands)

## Setup

```bash
git clone https://github.com/R2Rprogpower/guzleaks.git .
cp .env.example .env
docker compose up -d --build

docker compose exec -T app composer install
docker compose exec -T app php artisan key:generate

# One-time package publishes used in this project
docker compose exec -T app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec -T app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan optimize:clear

make install-hooks
```

## Service URLs

- API / app (Nginx): http://localhost:8080
- PostgreSQL: localhost:5432
- Redis: localhost:6379
- pgAdmin: http://localhost:5050
  - Email: admin@example.com
  - Password: admin

## Database defaults (.env)

- DB host: db
- DB port: 5432
- DB name: app
- DB user: app
- DB password: app

## Useful commands

```bash
# Start/stop
make up
make build

# Code quality
make fmt
make lint
make test
make check

# Laravel commands
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan migrate:fresh
docker compose exec -T app php artisan route:list --path=api
```

## Notes

- This project uses PostgreSQL, so use pgAdmin.
- If you run Artisan locally (outside Docker), ensure required PHP extensions are installed.
