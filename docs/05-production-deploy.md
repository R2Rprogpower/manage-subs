# Production Deployment (Blue-Green)

This is a reusable production base setup for this app and future modules/features.

## How it works

- Two stacks run alternately: blue and green.
- App traffic is proxied by Caddy to the active stack (18081 or 18082, loopback only).
- PgAdmin traffic is proxied by Caddy to the active stack PgAdmin (15050 or 15051, loopback only).
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

## Requirements on VPS

- Docker Engine running and user in docker group
- Docker Compose v2 (`docker compose`)
- Caddy installed and managed by systemd (`caddy.service`)
- Ports 80 and 443 free for Caddy (disable LiteSpeed/Nginx/Apache on host if present)

## First-time setup

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

Set minimum values in `/opt/app/.env`:

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

## Deploy commands

First deploy:

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

## Deploy modes

- Manual: run deploy command on VPS.
- Automatic: `.github/workflows/ci.yml` runs checks, then deploys on push to main.
- Parallel deploy protection: `scripts/deploy.sh` lock file (`/opt/app/.deploy.lock`).

For multi-app template usage, see `docs/08-template-repo-workflow.md`.

In `.github/workflows/ci.yml`, update deploy job env values per app:

- `DEPLOY_SETUP_DIR`
- `APP_DOMAIN`
- `PGADMIN_DOMAIN`

## Required GitHub secrets

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`
- `DEPLOY_WEBHOOK_URL` (optional, for success/failure notifications)

`DEPLOY_WEBHOOK_URL` should be an incoming webhook endpoint that accepts JSON with a `text` field.

Slack incoming webhook format example:

```text
https://hooks.slack.com/services/<workspace>/<channel>/<webhook-token>
```

If secret is not set, notifications are skipped.

## Access URLs

- API: https://ruslanrahimov.space
- PgAdmin: https://pgadmin.ruslanrahimov.space

## PgAdmin PostgreSQL connection values

- Hostname/address: db
- Port: 5432
- Database: app (or postgres)
- Username: app
- Password: value of `DB_PASSWORD` in `/opt/app/.env`
