# Security and Troubleshooting

## Security notes

### OpenAPI docs security

- Keep `SCRAMBLE_PROTECT_DOCS=true` in production.
- Do not expose docs publicly unless required.
- If temporary public docs are needed, enable only briefly and revert.

### PostgreSQL security

- PostgreSQL is not exposed publicly in deploy compose.
- Do not publish 5432 to 0.0.0.0.
- Use strong `DB_PASSWORD` and rotate if leaked.
- Keep DB access internal (app/pgAdmin over Docker network).
- Deploy tests run against a separate DB (`DEPLOY_TEST_DB_NAME`, default `app_deploy_test`).

### PgAdmin security

- Use strong `PGADMIN_DEFAULT_PASSWORD`.
- Restrict PgAdmin DNS to trusted admins when possible.
- Optional hardening: additional Caddy auth or IP allowlist for pgadmin subdomain.

## Troubleshooting quick hits

- `address already in use :80`: stop host LiteSpeed/Nginx/Apache (`sudo systemctl stop lsws && sudo systemctl disable lsws`).
- `Cannot connect to Docker daemon`: start Docker and ensure deploy user is in docker group.
- `MissingAppKeyException`: deploy script auto-generates APP_KEY in `.env` if empty.
- `File not found` on `/api/health`: pull latest deploy script and redeploy.
- `Bind for 127.0.0.1:<port> failed: port is already allocated`: two apps are using the same host port. Set unique `BLUE_HTTP_PORT`, `GREEN_HTTP_PORT`, `BLUE_PGADMIN_PORT`, `GREEN_PGADMIN_PORT` per repo in GitHub Variables. See port map in `docs/08-template-repo-workflow.md`.
- `sudo: a password is required` during deploy: add passwordless sudo for the deploy user. See `docs/05-production-deploy.md` for the sudoers entry.
- Domain returns response from wrong app: check `/etc/caddy/conf.d/<app>.caddy` — the port must match the app's active color port. Run `curl http://127.0.0.1:<port>/api/health` directly on the VPS to verify which app is on which port.
- Caddyfile not importing snippets: run `sudo cat /etc/caddy/Caddyfile` and confirm it contains `import /etc/caddy/conf.d/*.caddy`. If not, the next deploy will fix it, or write it manually:
  ```bash
  sudo tee /etc/caddy/Caddyfile > /dev/null <<'EOF'
  {
    email ops@example.com
  }

  import /etc/caddy/conf.d/*.caddy
  EOF
  sudo systemctl reload caddy
  ```

## Relevant files

| File | Purpose |
|------|---------|
| `docker-compose.deploy.yml` | Deploy stack (app, nginx, db, redis, pgadmin) |
| `docker-compose.blue.yml` | Blue color host/loopback ports |
| `docker-compose.green.yml` | Green color host/loopback ports |
| `scripts/deploy.sh` | Blue-green deployment orchestration |
| `scripts/rollback.sh` | One-command rollback to previous color |
| `scripts/backup-db.sh` | PostgreSQL compressed backup + retention |
| `.github/workflows/ci.yml` | CI checks + auto deploy + notifications |
