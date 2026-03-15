# Rollback and Backups

## One-command rollback

Rollback to previous color:

```bash
cd /opt/app/setup
DEPLOY_BASE_DIR=/opt/app \
DOMAIN=api.example.com \
PGADMIN_DOMAIN=pgadmin.example.com \
BLUE_HTTP_PORT=18083 \
GREEN_HTTP_PORT=18084 \
BLUE_PGADMIN_PORT=15052 \
GREEN_PGADMIN_PORT=15053 \
bash scripts/rollback.sh
```

What rollback does:

- Starts the previously active color stack
- Writes the per-app Caddy snippet with the previous color's port
- Reloads Caddy
- Updates active color state

## Nightly PostgreSQL backups

Backup script:

- `scripts/backup-db.sh`

Recommended cron (daily at 03:00):

```bash
crontab -e
```

Add:

```cron
0 3 * * * DEPLOY_BASE_DIR=/opt/app bash /opt/app/setup/scripts/backup-db.sh >> /opt/app/backups/backup.log 2>&1
```

Backups path:

- `/opt/app/backups/*.sql.gz`

Retention:

- 14 days by default (`BACKUP_RETENTION_DAYS`)

## Restore example

```bash
set -a; source /opt/app/.env; set +a
ACTIVE=$(cat /opt/app/.active)
APP_SLUG="app"   # basename of DEPLOY_BASE_DIR, lower-cased
BACKUP_FILE=/opt/app/backups/<db_name>_YYYY-MM-DD_HHMMSS.sql.gz

gunzip -c "$BACKUP_FILE" | \
  docker compose -p "app_${APP_SLUG}_${ACTIVE}" \
    -f "/opt/app/$ACTIVE/docker-compose.deploy.yml" \
    -f "/opt/app/$ACTIVE/docker-compose.$ACTIVE.yml" \
    exec -T db env PGPASSWORD="$DB_PASSWORD" psql -U "$DB_USERNAME" "$DB_DATABASE"
```

> The compose project name is `app_<app_slug>_<color>`. For an app at `/opt/craftChronicles`, the slug is `craftchronicles` and the project name is e.g. `app_craftchronicles_blue`.
