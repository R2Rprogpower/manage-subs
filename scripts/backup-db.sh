#!/usr/bin/env bash

set -euo pipefail

DEFAULT_BASE_DIR="/opt/app"
SCRIPT_PARENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [[ "$(basename "$SCRIPT_PARENT_DIR")" == "setup" ]]; then
  DEFAULT_BASE_DIR="$(dirname "$SCRIPT_PARENT_DIR")"
fi

BASE_DIR="${DEPLOY_BASE_DIR:-$DEFAULT_BASE_DIR}"
STATE_FILE="$BASE_DIR/.active"
ENV_FILE="$BASE_DIR/.env"
BACKUP_DIR="${BACKUP_DIR:-$BASE_DIR/backups}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
APP_NAME="${APP_NAME:-$(basename "$BASE_DIR")}"
APP_SLUG="$(printf '%s' "$APP_NAME" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '_')"

if [[ -z "$APP_SLUG" ]]; then
  APP_SLUG="app"
fi

if [[ ! -f "$ENV_FILE" ]]; then
  echo "ERROR: env file not found: $ENV_FILE" >&2
  exit 1
fi

DB_NAME="$(grep -E '^DB_DATABASE=' "$ENV_FILE" | tail -n1 | cut -d'=' -f2- || true)"
DB_USER="$(grep -E '^DB_USERNAME=' "$ENV_FILE" | tail -n1 | cut -d'=' -f2- || true)"
DB_PASS="$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | tail -n1 | cut -d'=' -f2- || true)"

if [[ -z "$DB_NAME" || -z "$DB_USER" ]]; then
  echo "ERROR: DB_DATABASE/DB_USERNAME must be set in $ENV_FILE" >&2
  exit 1
fi

ACTIVE="$(cat "$STATE_FILE" 2>/dev/null || echo "blue")"
if [[ "$ACTIVE" != "blue" && "$ACTIVE" != "green" ]]; then
  ACTIVE="blue"
fi

mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$(date +%F_%H%M%S).sql.gz"

if docker compose version >/dev/null 2>&1; then
  COMPOSE_BIN=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_BIN=(docker-compose)
else
  echo "ERROR: Neither 'docker compose' nor 'docker-compose' is available." >&2
  exit 1
fi

TARGET_DIR="$BASE_DIR/$ACTIVE"
if [[ ! -d "$TARGET_DIR" ]]; then
  echo "ERROR: active stack directory not found: $TARGET_DIR" >&2
  exit 1
fi

cd "$TARGET_DIR"
COMPOSE_PROJECT_NAME="app_${APP_SLUG}_$ACTIVE" "${COMPOSE_BIN[@]}" exec -T db env PGPASSWORD="$DB_PASS" pg_dump -U "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_FILE"

find "$BACKUP_DIR" -type f -name '*.sql.gz' -mtime +"$RETENTION_DAYS" -delete

echo "Backup complete: $BACKUP_FILE"
