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

## Module-local database structure

Each module can define its own database artifacts directly inside the module:

- `app/Modules/<Module>/Database/Migrations/*.php`
- `app/Modules/<Module>/Database/Seeders/*Seeder.php`
- `app/Modules/<Module>/Database/Factories/*Factory.php`

How it works:

- Module migrations are auto-loaded by `App\Providers\ModuleDatabaseServiceProvider`.
- Module seeders are auto-discovered by `database/seeders/DatabaseSeeder.php`.
- Factories can live in the same module and be referenced from module models via `newFactory()`.

Example commands:

```bash
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan db:seed
docker compose exec -T app php artisan migrate:fresh --seed
```

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

## OpenAPI / API docs

This project uses Scramble for OpenAPI generation.

- Docs UI: `http://localhost:8080/docs/api`
- OpenAPI JSON: `http://localhost:8080/docs/api.json`

Docs protection is controlled in `config/scramble.php` via `SCRAMBLE_PROTECT_DOCS`.
By default in this project (`false`), docs are publicly accessible.

### Generate OpenAPI file

```bash
# Export OpenAPI JSON to default path (configured in config/scramble.php)
docker compose exec -T app php artisan scramble:export

# Export OpenAPI JSON to a custom file path
docker compose exec -T app php artisan scramble:export --path=openapi.json

# Analyze documentation generation issues
docker compose exec -T app php artisan scramble:analyze
```

Recommended flow:

1. `Sign Up`
2. `Login (No MFA)`
3. `Setup MFA`
4. `Verify MFA`
5. `Login (With MFA)`

## Notes

- This project uses **PostgreSQL**, so use **pgAdmin**.
- If you run Artisan locally (outside Docker), ensure required PHP extensions are installed.


## Production deployment (blue-green)

### How it works

- Two identical stacks run alternately: **blue** (port 8081) and **green** (port 8082).
- **Caddy** runs on the host and proxies live traffic to the active stack.
- Each deploy: pull code → build new stack → health check → migrate → switch Caddy → stop old stack.
- Zero-downtime: traffic only switches after the new stack passes the health check.

### VPS first-time setup

```bash
# 1. Install Caddy
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install caddy

# 2. Install Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER && newgrp docker

# 3. Create base dir and drop your .env file
sudo mkdir -p /opt/app
sudo chown $USER:$USER /opt/app
cp .env.production /opt/app/.env      # edit DB_PASSWORD, APP_KEY, APP_URL etc.

# 4. First deploy
DOMAIN=yourdomain.com ./scripts/deploy.sh \
  --repo git@github.com:R2Rprogpower/guzleaks.git \
  --branch main \
  --env /opt/app/.env
```

### Every subsequent deploy

```bash
DOMAIN=yourdomain.com ./scripts/deploy.sh
```

The script (`scripts/deploy.sh`) will automatically:
1. Pull latest code into the inactive color dir (`/opt/app/blue` or `/opt/app/green`)
2. Build Docker image
3. Start new stack
4. Wait for `/api/health` to return 200
5. Run `php artisan migrate --force` + `optimize`
6. Reload Caddy to point at new stack
7. Stop old stack

### Relevant files

| File | Purpose |
|------|---------|
| `docker-compose.prod.yml` | Production overrides (no source mount, no pgAdmin, restart policies) |
| `docker-compose.blue.yml` | Blue stack port mapping (8081) |
| `docker-compose.green.yml` | Green stack port mapping (8082) |
| `docker/caddy/Caddyfile` | Caddy template (host reverse proxy) |
| `scripts/deploy.sh` | Blue-green deploy script |

### Environment variables required on VPS

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=<generate: php artisan key:generate --show>
DB_PASSWORD=<strong password>
SCRAMBLE_PROTECT_DOCS=true
```

### Rollback

```bash
# Manually switch Caddy back to old port and re-start old stack
# e.g. if blue just went live and you want to roll back to green:
sudo sed -i 's/8081/8082/' /etc/caddy/Caddyfile
sudo systemctl reload caddy
cd /opt/app/green && docker compose -p app_green \
  -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.green.yml up -d
```

## TODO

- API versioning (/api/v1)
- Automated CD via GitHub Actions SSH deploy
