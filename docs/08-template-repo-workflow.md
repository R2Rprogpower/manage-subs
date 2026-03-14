# Template Repo Workflow (Multi-App)

Use this repository as a base template, then create one repository per app.

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
git clone https://github.com/<you>/<base-repo>.git my-new-app
cd my-new-app
git remote remove origin
git remote add origin https://github.com/<you>/my-new-app.git
git push -u origin main
```

## 2) Configure CI/CD in the new repo

Set these GitHub **Secrets**:

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`
- `DEPLOY_WEBHOOK_URL` (optional)

Update deploy env values in `.github/workflows/ci.yml` (`deploy` job `env`):

- `DEPLOY_SETUP_DIR` (example: `/opt/my-new-app/setup`)
- `APP_DOMAIN` (example: `api.my-new-app.com`)
- `PGADMIN_DOMAIN` (example: `pgadmin.my-new-app.com`)

## 3) Prepare VPS directories for the new app

```bash
sudo mkdir -p /opt/my-new-app
sudo chown $USER:$USER /opt/my-new-app

git clone https://github.com/<you>/my-new-app.git /opt/my-new-app/setup
cp /opt/my-new-app/setup/.env.example /opt/my-new-app/.env
```

Then deploy from the setup directory:

```bash
cd /opt/my-new-app/setup
DOMAIN=api.my-new-app.com \
PGADMIN_DOMAIN=pgadmin.my-new-app.com \
bash scripts/deploy.sh \
  --repo https://github.com/<you>/my-new-app.git \
  --branch main \
  --env /opt/my-new-app/.env
```

## 4) Keep base and app changes separated

- Base improvements: land first in template repo, then cherry-pick/rebase into app repos.
- App features: keep only in that app repo.
- Avoid editing hardcoded app names/domains in shared scripts.

## 5) Per-app isolation checklist

- Unique VPS base directory (`/opt/<app-name>`)
- Unique domains (`APP_DOMAIN`, `PGADMIN_DOMAIN`)
- Unique `.env` secrets and DB passwords
- Separate GitHub repo secrets
