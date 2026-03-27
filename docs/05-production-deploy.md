# Production Deployment (Blue-Green)

This is a reusable production base setup for this app and future modules/features.

## How it works

- Two stacks run alternately: blue and green.
- App traffic is proxied by Caddy to the active stack (loopback only, ports set via `BLUE_HTTP_PORT`/`GREEN_HTTP_PORT`).
- PgAdmin traffic is proxied by Caddy to the active stack pgAdmin (loopback only, ports set via `BLUE_PGADMIN_PORT`/`GREEN_PGADMIN_PORT`).
- Each app writes only its own Caddy block to `/etc/caddy/conf.d/<APP_NAME>.caddy` — the global Caddyfile imports all snippets, so multiple apps on the same VPS don't overwrite each other.
- Compose project names are namespaced as `app_<app_slug>_<color>` (e.g. `app_craftchronicles_blue`) so containers never collide across apps.
- Deploy sequence is automated by `scripts/deploy.sh`:
  1. Pull code into inactive color (`/opt/<app>/blue` or `/opt/<app>/green`)
  2. Tear down any leftover containers from previous failed deploys
  3. Start stack (`docker-compose.deploy.yml` + color override)
  4. Install Composer dependencies
  5. Health check `/api/health`
  6. Run production migrations (`php artisan migrate --force`, never `migrate:fresh`)
  7. Create isolated test DB on the new color (`app_deploy_test` by default)
  8. Run unit + feature tests against isolated test DB
  9. Write per-app Caddy snippet and reload Caddy
  10. Stop old stack

## Requirements on VPS

- Docker Engine running and user in docker group
- Docker Compose v2 (`docker compose`)
- Caddy installed and managed by systemd (`caddy.service`)
- Ports 80 and 443 free for Caddy (disable LiteSpeed/Nginx/Apache on host if present)
- `flock` available (`util-linux` package)
- Passwordless sudo for deploy user for: `/usr/bin/install`, `/usr/bin/mkdir`, `/usr/bin/systemctl`, `/usr/bin/caddy`

## First-time setup

```bash
# Create base dir (use /opt/<app-name>)
sudo mkdir -p /opt/app
sudo chown $USER:$USER /opt/app

# Clone setup repo
git clone https://github.com/R2Rprogpower/guzleaks.git /opt/app/setup

# Prepare env (APP_KEY can be empty; deploy script auto-generates if missing)
cp /opt/app/setup/.env.example /opt/app/.env
```

Set minimum values in `/opt/app/.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=<strong-password>

SCRAMBLE_PROTECT_DOCS=true

PGADMIN_DEFAULT_EMAIL=admin@example.com
PGADMIN_DEFAULT_PASSWORD=<strong-password>
ACME_EMAIL=ops@example.com
```

## Deploy commands

First deploy:

```bash
DEPLOY_BASE_DIR=/opt/app \
DOMAIN=api.example.com \
PGADMIN_DOMAIN=pgadmin.example.com \
ACME_EMAIL=ops@example.com \
BLUE_HTTP_PORT=18083 \
GREEN_HTTP_PORT=18084 \
BLUE_PGADMIN_PORT=15052 \
GREEN_PGADMIN_PORT=15053 \
bash scripts/deploy.sh \
  --repo https://github.com/R2Rprogpower/guzleaks.git \
  --branch main \
  --env /opt/app/.env
```

Subsequent deploys (after repo and env are saved in `/opt/app/config`):

```bash
cd /opt/app/setup && git pull origin main

DEPLOY_BASE_DIR=/opt/app \
DOMAIN=api.example.com \
PGADMIN_DOMAIN=pgadmin.example.com \
ACME_EMAIL=ops@example.com \
BLUE_HTTP_PORT=18083 \
GREEN_HTTP_PORT=18084 \
BLUE_PGADMIN_PORT=15052 \
GREEN_PGADMIN_PORT=15053 \
bash scripts/deploy.sh --env /opt/app/.env
```

## Deploy modes

- **Manual**: run deploy command on VPS.
- **Automatic (CI)**: `.github/workflows/ci.yml` runs checks, then deploys on push to `main`. All values come from GitHub repo variables/secrets — no need to edit the workflow file.
- **Parallel deploy protection**: `scripts/deploy.sh` uses a lock file (`/opt/<app>/.deploy.lock`).

For multi-app template usage, see `docs/08-template-repo-workflow.md`.

## Required GitHub repository variables

Set these under `Settings → Secrets and variables → Actions → Variables` **per repo**:

| Variable | Example |
|---|---|
| `DEPLOY_SETUP_DIR` | `/opt/app/setup` |
| `DEPLOY_ENV_FILE` | `/opt/app/.env` |
| `APP_DOMAIN` | `api.example.com` |
| `PGADMIN_DOMAIN` | `pgadmin.example.com` |
| `ACME_EMAIL` | `ops@example.com` |
| `BLUE_HTTP_PORT` | `18083` |
| `GREEN_HTTP_PORT` | `18084` |
| `BLUE_PGADMIN_PORT` | `15052` |
| `GREEN_PGADMIN_PORT` | `15053` |

⚠️ **Port values must be unique per app on a shared VPS.** If two apps use the same ports, Docker will fail to start the second app's containers.

## Required GitHub secrets

| Secret | Purpose |
|---|---|
| `VPS_HOST` | VPS public IP (`curl -4 ifconfig.me`) |
| `VPS_USER` | SSH user (e.g. `deploy`) |
| `VPS_SSH_KEY` | Private key content (`cat ~/.ssh/<key>`) |
 ssh-keygen -t ed25519 -f /root/.ssh/ms_deploy_key_nopass -C "ms-deploy"
  cat /root/.ssh/ms_deploy_key_nopass.pub >> /root/.ssh/authorized_keys
  cat /root/.ssh/ms_deploy_key_nopass
  
| `DEPLOY_WEBHOOK_URL` | Optional — Slack/webhook URL for deploy notifications |

Important:

- Never leave placeholder domains like `your-domain.tld` in deploy configuration.
- `ACME_EMAIL` must be a real email (not `@example.com`) or certificate issuance will fail.

## Caddy multi-app setup

The deploy script manages Caddy as follows:

- Main Caddyfile (`/etc/caddy/Caddyfile`) is written once with global settings and `import /etc/caddy/conf.d/*.caddy`.
- Each app's Caddy config is written to `/etc/caddy/conf.d/<APP_NAME>.caddy` only — other apps' snippets are never touched.
- `APP_NAME` defaults to `basename` of `DEPLOY_BASE_DIR` (e.g. `app`, `craftChronicles`).

Example snippet written by deploy for each app:

```
api.example.com {
    reverse_proxy 127.0.0.1:18083
}

pgadmin.example.com {
    reverse_proxy 127.0.0.1:15052
}
```

## Sudo requirements for deploy user

Add to `/etc/sudoers.d/deploy-caddy` on the VPS:

```
deploy ALL=(root) NOPASSWD: /usr/bin/install, /usr/bin/mkdir, /usr/bin/systemctl reload caddy, /usr/bin/systemctl restart caddy, /usr/bin/caddy *
```

## PgAdmin PostgreSQL connection values

- Hostname/address: `db`
- Port: `5432`
- Database: `app` (or `postgres`)
- Username: value of `DB_USERNAME` in `.env`
- Password: value of `DB_PASSWORD` in `.env`
