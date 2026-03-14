# Template Repo Workflow (Multi-App)

Use this repository as a base template, then create one repository per app.

Base template repository:

- `https://github.com/R2Rprogpower/guzleaks`

## Recommended model

- Keep this repository as your foundation template.
- Create a new repo from it for each app (`app-a`, `app-b`, etc.).
- Deploy each app to its own VPS path and domains.

## 1) Create a new app repo from this base

Option A (GitHub UI):

- Click **Use this template** on the base repository.
- Create new repository (for example `my-new-app`).

Option B (manual clone + new remote):

```bash
git clone https://github.com/R2Rprogpower/guzleaks.git my-new-app
cd my-new-app
git remote remove origin
git remote add origin https://github.com/R2Rprogpower/my-new-app.git
git push -u origin main
```

## 2) Configure CI/CD in the new repo

Set these GitHub **Secrets**:

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`
- `DEPLOY_WEBHOOK_URL` (optional)

Important:

- Secrets do not copy automatically between repositories unless you use org-level secrets.
- Use the same values as your current working repo if you want to deploy to the same VPS.

Update deploy env values in `.github/workflows/ci.yml` (`deploy` job `env`):

- `DEPLOY_SETUP_DIR` (example: `/opt/my-new-app/setup`)
- `DEPLOY_ENV_FILE` (example: `/opt/my-new-app/.env`)
- `APP_DOMAIN` (example: `api.my-new-app.com`)
- `PGADMIN_DOMAIN` (example: `pgadmin.my-new-app.com`)
- `ACME_EMAIL` (example: `ops@my-new-app.com`)

## 3) Prepare VPS directories for the new app

```bash
sudo mkdir -p /opt/my-new-app
sudo chown $USER:$USER /opt/my-new-app

git clone https://github.com/<you>/my-new-app.git /opt/my-new-app/setup
cp /opt/my-new-app/setup/.env.example /opt/my-new-app/.env
```

Replace repository URL in the command above with:

- `https://github.com/R2Rprogpower/my-new-app.git`

Then deploy from the setup directory:

```bash
cd /opt/my-new-app/setup
DOMAIN=api.my-new-app.com \
PGADMIN_DOMAIN=pgadmin.my-new-app.com \
bash scripts/deploy.sh \
  --repo https://github.com/R2Rprogpower/my-new-app.git \
  --branch main \
  --env /opt/my-new-app/.env
```

## 6) Exact checklist (copy/paste order)

1. Create a new repository in GitHub (for example `my-new-app`) under `R2Rprogpower`.
2. Run locally:

```bash
git clone https://github.com/R2Rprogpower/guzleaks.git my-new-app
cd my-new-app
git remote remove origin
git remote add origin https://github.com/R2Rprogpower/my-new-app.git
git push -u origin main
```

3. In new repo GitHub settings, add secrets with same values as current repo:
   - `VPS_HOST`
   - `VPS_USER`
   - `VPS_SSH_KEY`
   - `DEPLOY_WEBHOOK_URL` (optional)
4. Edit `.github/workflows/ci.yml` in new repo:
   - `DEPLOY_SETUP_DIR=/opt/my-new-app/setup`
  - `DEPLOY_ENV_FILE=/opt/my-new-app/.env`
   - `APP_DOMAIN=api.my-new-app.com`
   - `PGADMIN_DOMAIN=pgadmin.my-new-app.com`
  - `ACME_EMAIL=ops@my-new-app.com`
5. On VPS:

```bash
sudo mkdir -p /opt/my-new-app
sudo chown $USER:$USER /opt/my-new-app
git clone https://github.com/R2Rprogpower/my-new-app.git /opt/my-new-app/setup
cp /opt/my-new-app/setup/.env.example /opt/my-new-app/.env
```

6. Set production values in `/opt/my-new-app/.env` (DB passwords, app URL, pgAdmin credentials).
7. First deploy on VPS:

```bash
cd /opt/my-new-app/setup
DOMAIN=api.my-new-app.com \
PGADMIN_DOMAIN=pgadmin.my-new-app.com \
bash scripts/deploy.sh \
  --repo https://github.com/R2Rprogpower/my-new-app.git \
  --branch main \
  --env /opt/my-new-app/.env
```

8. After first deploy, future deploys happen automatically on push to `main` in `my-new-app`.

## 4) Keep base and app changes separated

- Base improvements: land first in template repo, then cherry-pick/rebase into app repos.
- App features: keep only in that app repo.
- Avoid editing hardcoded app names/domains in shared scripts.

## 5) Per-app isolation checklist

- Unique VPS base directory (`/opt/<app-name>`)
- Unique domains (`APP_DOMAIN`, `PGADMIN_DOMAIN`)
- Unique `.env` secrets and DB passwords
- Separate GitHub repo secrets
