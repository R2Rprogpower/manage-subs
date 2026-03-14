# API (Laravel 12)

## Requirements

- Docker + Docker Compose
- GNU Make (optional, for helper commands)

## Local setup

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
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`
- pgAdmin: http://localhost:5050
	- Email: `admin@example.com`
	- Password: `admin`

## Database defaults (.env)

- DB host: `db`
- DB port: `5432`
- DB name: `app`
- DB user: `app`
- DB password: `app`

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

## API overview

All API routes are loaded from module route files via `routes/api.php`:

- `app/Modules/Auth/api.php`
- `app/Modules/Permissions/api.php`
- `app/Modules/Users/api.php`

### Auth + MFA

- `POST /api/auth/signup`
- `POST /api/auth/login` (MFA token is required only when MFA is enabled for the user)
- `POST /api/auth/mfa/setup`
- `POST /api/auth/mfa/verify`
- `POST /api/auth/logout` (auth:sanctum)
- `POST /api/auth/tokens/revoke` (auth:sanctum)

### Protected resources (auth:sanctum)

- Users: `/api/users...`
- Roles: `/api/roles...`
- Permissions: `/api/permissions...`

## Postman

Import the provided collection:

- `postman/Auth-2FA.postman_collection.json`

Recommended flow:

1. `Sign Up`
2. `Login (No MFA)`
3. `Setup MFA`
4. `Verify MFA`
5. `Login (With MFA)`

## Notes

- This project uses **PostgreSQL**, so use **pgAdmin**.
- If you run Artisan locally (outside Docker), ensure required PHP extensions are installed.