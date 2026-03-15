#!/usr/bin/env bash

set -euo pipefail

DEFAULT_BASE_DIR="/opt/app"
SCRIPT_PARENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [[ "$(basename "$SCRIPT_PARENT_DIR")" == "setup" ]]; then
  DEFAULT_BASE_DIR="$(dirname "$SCRIPT_PARENT_DIR")"
fi

BASE_DIR="${DEPLOY_BASE_DIR:-$DEFAULT_BASE_DIR}"
STATE_FILE="$BASE_DIR/.active"
BASE_COMPOSE_FILE="docker-compose.deploy.yml"
CADDY_FILE="/etc/caddy/Caddyfile"
CADDY_CONF_DIR="/etc/caddy/conf.d"
APP_NAME="${APP_NAME:-$(basename "$BASE_DIR")}"
APP_SLUG="$(printf '%s' "$APP_NAME" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '_')"

if [[ -z "$APP_SLUG" ]]; then
  APP_SLUG="app"
fi

DOMAIN="${DOMAIN:-localhost}"
PGADMIN_DOMAIN="${PGADMIN_DOMAIN:-pgadmin.${DOMAIN}}"
BLUE_HTTP_PORT="${BLUE_HTTP_PORT:-18081}"
GREEN_HTTP_PORT="${GREEN_HTTP_PORT:-18082}"
BLUE_PGADMIN_PORT="${BLUE_PGADMIN_PORT:-15050}"
GREEN_PGADMIN_PORT="${GREEN_PGADMIN_PORT:-15051}"

run_as_root() {
  if [[ "$(id -u)" -eq 0 ]]; then
    "$@"
  else
    sudo -n "$@"
  fi
}

if docker compose version >/dev/null 2>&1; then
  COMPOSE_BIN=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_BIN=(docker-compose)
else
  echo "ERROR: Neither 'docker compose' nor 'docker-compose' is available." >&2
  exit 1
fi

ACTIVE="$(cat "$STATE_FILE" 2>/dev/null || true)"
if [[ "$ACTIVE" != "blue" && "$ACTIVE" != "green" ]]; then
  echo "ERROR: Active color is unknown. State file: $STATE_FILE" >&2
  exit 1
fi

if [[ "$ACTIVE" == "blue" ]]; then
  TARGET="green"
  TARGET_HTTP_PORT="$GREEN_HTTP_PORT"
  TARGET_PGADMIN_PORT="$GREEN_PGADMIN_PORT"
else
  TARGET="blue"
  TARGET_HTTP_PORT="$BLUE_HTTP_PORT"
  TARGET_PGADMIN_PORT="$BLUE_PGADMIN_PORT"
fi

TARGET_DIR="$BASE_DIR/$TARGET"
if [[ ! -d "$TARGET_DIR" ]]; then
  echo "ERROR: Target color directory not found: $TARGET_DIR" >&2
  exit 1
fi

echo "Rolling back from $ACTIVE to $TARGET ..."

cd "$TARGET_DIR"
COMPOSE_PROJECT_NAME="app_${APP_SLUG}_$TARGET" "${COMPOSE_BIN[@]}" \
  -f "$BASE_COMPOSE_FILE" \
  -f "docker-compose.$TARGET.yml" \
  up -d --build --remove-orphans

# Update only this app's per-app snippet; other apps' snippets are untouched.
run_as_root mkdir -p "$CADDY_CONF_DIR"
CADDY_APP_CONF="$CADDY_CONF_DIR/${APP_NAME}.caddy"
CADDY_TMP_FILE="$(mktemp)"
cat > "$CADDY_TMP_FILE" <<EOF
${DOMAIN} {
    reverse_proxy 127.0.0.1:${TARGET_HTTP_PORT}
}

${PGADMIN_DOMAIN} {
    reverse_proxy 127.0.0.1:${TARGET_PGADMIN_PORT}
}
EOF

if [[ -w "$CADDY_CONF_DIR" ]]; then
  install -m 644 "$CADDY_TMP_FILE" "$CADDY_APP_CONF"
else
  run_as_root install -m 644 "$CADDY_TMP_FILE" "$CADDY_APP_CONF"
fi
rm -f "$CADDY_TMP_FILE"

if command -v caddy >/dev/null 2>&1; then
  run_as_root caddy validate --config "$CADDY_FILE" --adapter caddyfile
fi

if command -v systemctl >/dev/null 2>&1; then
  run_as_root systemctl reload caddy || run_as_root systemctl restart caddy
elif command -v caddy >/dev/null 2>&1; then
  run_as_root caddy reload --config "$CADDY_FILE" --adapter caddyfile
else
  echo "ERROR: Cannot reload Caddy (no systemctl/caddy)." >&2
  exit 1
fi

echo "$TARGET" > "$STATE_FILE"
echo "Rollback complete. Active color: $TARGET"
