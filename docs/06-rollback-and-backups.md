# Rollback and Backups

## One-command rollback

Rollback to previous color:

```bash
cd /opt/app/setup
DOMAIN=ruslanrahimov.space \
PGADMIN_DOMAIN=pgadmin.ruslanrahimov.space \
bash scripts/rollback.sh
```

What rollback does:

- Starts previous color stack
- Switches Caddy back
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
0 3 * * * cd /opt/app/setup && bash scripts/backup-db.sh >> /opt/app/backups/backup.log 2>&1
```

Backups path:

- `/opt/app/backups/*.sql.gz`

Retention:

- 14 days by default (`BACKUP_RETENTION_DAYS`)

## Restore example

```bash
set -a; source /opt/app/.env; set +a
ACTIVE=$(cat /opt/app/.active)
BACKUP_FILE=/opt/app/backups/<db_name>_YYYY-MM-DD_HHMMSS.sql.gz

gunzip -c "$BACKUP_FILE" | \
  docker compose -p "app_$ACTIVE" \
    -f "/opt/app/$ACTIVE/docker-compose.deploy.yml" \
    -f "/opt/app/$ACTIVE/docker-compose.$ACTIVE.yml" \
    exec -T db env PGPASSWORD="$DB_PASSWORD" psql -U "$DB_USERNAME" "$DB_DATABASE"
```
