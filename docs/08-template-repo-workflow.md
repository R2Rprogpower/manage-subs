# Template Repo Workflow (Multi-App)

Use this repository as a base template, then create one repository per app.

Base template repository:

- `https://github.com/R2Rprogpower/guzleaks`

## Recommended model

- Keep this repository as your foundation template.
- Create a new repo from it for each app (e.g. `craftChronicles`, `my-new-app`).
- Deploy each app to its own VPS path, using unique loopback ports.
- Multiple apps can live on the same VPS — Caddy and Docker are fully isolated per app.

## Port allocation map

Each app needs 4 unique loopback ports. Never reuse ports across apps on the same VPS.

| App | `BLUE_HTTP_PORT` | `GREEN_HTTP_PORT` | `BLUE_PGADMIN_PORT` | `GREEN_PGADMIN_PORT` |
|---|---|---|---|---|
| guzleaks | 18083 | 18084 | 15052 | 15053 |
| craftChronicles | 18085 | 18086 | 15054 | 15055 |
| next app | 18087 | 18088 | 15056 | 15057 |

## 1) Create a new app repo from this base

Option A (GitHub UI):

- Click **Use this template** on the base repository.
- Create new repository (e.g. `my-new-app`).

Option B (manual clone + new remote):

```bash
git clone https://github.com/R2Rprogpower/guzleaks.git my-new-app
cd my-new-app
git remote remove origin
git remote add origin https://github.com/R2Rprogpower/my-new-app.git
git push -u origin main
```

## 2) Configure CI/CD for the new repo

Do **not** edit `.github/workflows/ci.yml`. All per-app values are read from GitHub **Repository Variables** automatically.

### GitHub Secrets (shared between repos if same VPS)

| Secret | Value |
|---|---|
| `VPS_HOST` | VPS public IP |
| `VPS_USER` | SSH user (e.g. `deploy`) |
| `VPS_SSH_KEY` | Private key content |
| `DEPLOY_WEBHOOK_URL` | Optional |

### GitHub Repository Variables (unique per repo)

Set under `Settings → Secrets and variables → Actions → Variables`:

| Variable | Example value |
|---|---|
| `DEPLOY_SETUP_DIR` | `/opt/my-new-app/setup` |
| `DEPLOY_ENV_FILE` | `/opt/my-new-app/.env` |
| `APP_DOMAIN` | `api.my-new-app.com` |
| `PGADMIN_DOMAIN` | `pgadmin.my-new-app.com` |
| `ACME_EMAIL` | `ops@my-new-app.com` |
| `BLUE_HTTP_PORT` | (next free block, see port map above) |
| `GREEN_HTTP_PORT` | |
| `BLUE_PGADMIN_PORT` | |
| `GREEN_PGADMIN_PORT` | |

⚠️ **Port variables are mandatory on a shared VPS.** If not set, the deploy falls back to defaults (`18081–18082`, `15050–15051`) which will collide with other apps.

## 3) Prepare VPS directories for the new app

```bash
sudo mkdir -p /opt/my-new-app
sudo chown $USER:$USER /opt/my-new-app

git clone https://github.com/R2Rprogpower/my-new-app.git /opt/my-new-app/setup
cp /opt/my-new-app/setup/.env.example /opt/my-new-app/.env
# Edit /opt/my-new-app/.env with production values
```

First deploy (run once manually to bootstrap):

```bash
cd /opt/my-new-app/setup
DEPLOY_BASE_DIR=/opt/my-new-app \
DOMAIN=api.my-new-app.com \
PGADMIN_DOMAIN=pgadmin.my-new-app.com \
ACME_EMAIL=ops@my-new-app.com \
BLUE_HTTP_PORT=18087 \
GREEN_HTTP_PORT=18088 \
BLUE_PGADMIN_PORT=15056 \
GREEN_PGADMIN_PORT=15057 \
bash scripts/deploy.sh \
  --repo https://github.com/R2Rprogpower/my-new-app.git \
  --branch main \
  --env /opt/my-new-app/.env
```

After the first deploy, all subsequent deploys run automatically via GitHub Actions on push to `main`.

## 4) How multi-app Caddy isolation works

- The global Caddyfile (`/etc/caddy/Caddyfile`) contains only:
  ```
  {
    email ops@example.com
  }

  import /etc/caddy/conf.d/*.caddy
  ```
- Each app's deploy writes only its own snippet to `/etc/caddy/conf.d/<APP_NAME>.caddy`.
- Other apps' snippets are never touched during a deploy or rollback.
- `APP_NAME` = `basename` of `DEPLOY_BASE_DIR` (auto-derived, no config needed).

## 5) Exact checklist (copy/paste order)

1. Create new GitHub repo under `R2Rprogpower` (e.g. `my-new-app`).
2. Clone locally and push:
   ```bash
   git clone https://github.com/R2Rprogpower/guzleaks.git my-new-app
   cd my-new-app
   git remote remove origin
   git remote add origin https://github.com/R2Rprogpower/my-new-app.git
   git push -u origin main
   ```
3. In new repo settings, add **Secrets**: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` (same values as existing repo if same VPS).
4. In new repo settings, add **Variables** (all unique to this app — see tables above).
5. On VPS:
   ```bash
   sudo mkdir -p /opt/my-new-app && sudo chown $USER:$USER /opt/my-new-app
   git clone https://github.com/R2Rprogpower/my-new-app.git /opt/my-new-app/setup
   cp /opt/my-new-app/setup/.env.example /opt/my-new-app/.env
   # Edit /opt/my-new-app/.env with production values
   ```
6. Run first manual deploy (command in section 3 above).
7. Push any commit to `main` in new repo to verify CI auto-deploy works end to end.

## 6) Keep base and app changes separated

- Base improvements: land first in template repo (`guzleaks`), then cherry-pick or merge into app repos.
- App-specific features: keep only in that app repo.
- Never hardcode app names, domains, or ports in shared scripts — use env vars.

## 7) Per-app isolation checklist

- [ ] Unique VPS base directory (`/opt/<app-name>`)
- [ ] Unique domains (`APP_DOMAIN`, `PGADMIN_DOMAIN`)
- [ ] Unique port block (all four port variables)
- [ ] Unique `.env` (DB passwords, APP_KEY, app URL)
- [ ] Separate GitHub repo with its own Variables set
