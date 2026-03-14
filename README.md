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

- Two stacks run alternately: **blue** and **green**.
- App traffic is proxied by **Caddy** to the active stack (`18081` or `18082`, loopback only).
- PgAdmin traffic is proxied by **Caddy** to the active stack PgAdmin (`15050` or `15051`, loopback only).
- Deploy sequence is automated by `scripts/deploy.sh`:
  1. Pull code into inactive color (`/opt/app/blue` or `/opt/app/green`)
  2. Start stack (`docker-compose.deploy.yml` + color override)
  3. Install Composer dependencies
  4. Health check `/api/health`
  5. Run production migrations (`php artisan migrate --force`, never `migrate:fresh`)
  6. Create isolated test DB on the new color (`app_deploy_test` by default)
  7. Run unit + feature tests against isolated test DB
  8. Update and validate Caddy config, switch traffic
  9. Stop old stack

### Requirements on VPS

- Docker Engine running and user in `docker` group
- **Docker Compose v2** (`docker compose`)
- Caddy installed and managed by systemd (`caddy.service`)
- Port `80` and `443` must be free for Caddy (disable LiteSpeed/Nginx/Apache on host if present)

### First-time setup

```bash
# Create base dir
sudo mkdir -p /opt/app
sudo chown $USER:$USER /opt/app

# Clone setup repo
git clone https://github.com/R2Rprogpower/guzleaks.git /opt/app/setup
cd /opt/app/setup

# Prepare env (APP_KEY can be empty; deploy script auto-generates if missing)
cp .env.example /opt/app/.env
```

Set production values in `/opt/app/.env` (minimum):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ruslanrahimov.space

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=<strong-password>

SCRAMBLE_PROTECT_DOCS=true

PGADMIN_DEFAULT_EMAIL=admin@ruslanrahimov.space
PGADMIN_DEFAULT_PASSWORD=<strong-password>
```

Run first deploy:

```bash
DOMAIN=ruslanrahimov.space \
PGADMIN_DOMAIN=pgadmin.ruslanrahimov.space \
bash scripts/deploy.sh \
  --repo https://github.com/R2Rprogpower/guzleaks.git \
  --branch main \
  --env /opt/app/.env
```

Subsequent deploys:

```bash
cd /opt/app/setup
git pull origin main

DOMAIN=ruslanrahimov.space \
PGADMIN_DOMAIN=pgadmin.ruslanrahimov.space \
bash scripts/deploy.sh --env /opt/app/.env
```

### Deploy modes

- Manual (always available): run the command above on VPS after pushing to `main`.
- Automatic: `.github/workflows/deploy.yml` runs on every push to `main` and triggers the same VPS deploy script.

Required GitHub Secrets for auto-deploy:

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`
- `VPS_PORT` (optional, defaults to `22`)

### Access URLs

- API: `https://ruslanrahimov.space`
- PgAdmin: `https://pgadmin.ruslanrahimov.space`

### PgAdmin PostgreSQL connection values

When creating server in PgAdmin:

- Hostname/address: `db`
- Port: `5432`
- Database: `app` (or `postgres`)
- Username: `app`
- Password: value of `DB_PASSWORD` in `/opt/app/.env`

### Security notes (important)

#### OpenAPI docs security

- Keep `SCRAMBLE_PROTECT_DOCS=true` in production.
- Do not expose docs publicly unless required.
- If temporary public docs are needed, enable only briefly and revert.

#### PostgreSQL security

- PostgreSQL is **not** exposed publicly in deploy compose; keep it that way.
- Do not publish `5432` to `0.0.0.0`.
- Use strong `DB_PASSWORD` and rotate if leaked.
- Keep DB access internal (app/pgAdmin over Docker network).
- Deploy tests run against a separate DB (`DEPLOY_TEST_DB_NAME`, default `app_deploy_test`) to avoid touching production data.

#### PgAdmin security

- Use strong `PGADMIN_DEFAULT_PASSWORD`.
- Restrict PgAdmin DNS to trusted admins only when possible.
- Optional hardening: add additional Caddy auth or IP allowlist for `pgadmin.*`.

### Relevant files

| File | Purpose |
|------|---------|
| `docker-compose.deploy.yml` | Deploy stack (app, nginx, db, redis, pgadmin) |
| `docker-compose.blue.yml` | Blue color host/loopback ports |
| `docker-compose.green.yml` | Green color host/loopback ports |
| `scripts/deploy.sh` | Blue-green deployment orchestration |

### Troubleshooting quick hits

- `address already in use :80` when switching Caddy: stop host LiteSpeed/Nginx/Apache.
- `Cannot connect to Docker daemon`: start Docker and ensure deploy user is in docker group.
- `MissingAppKeyException`: deploy script now auto-generates `APP_KEY` in `/opt/app/.env` if empty.
- `File not found` on `/api/health`: ensure latest deploy script is pulled and deployment completed.

## TODO

- API versioning (/api/v1)
- Automated CD via GitHub Actions SSH deploy
