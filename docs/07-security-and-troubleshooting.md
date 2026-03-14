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

- `address already in use :80`: stop host LiteSpeed/Nginx/Apache.
- `Cannot connect to Docker daemon`: start Docker and ensure deploy user is in docker group.
- `MissingAppKeyException`: deploy script auto-generates APP_KEY in `/opt/app/.env` if empty.
- `File not found` on `/api/health`: pull latest deploy script and redeploy.

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
