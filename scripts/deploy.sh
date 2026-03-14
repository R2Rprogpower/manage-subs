#!/usr/bin/env bash
# Blue-green deploy script for Laravel + Docker Compose
# Usage: ./scripts/deploy.sh [--repo <git-repo-url>] [--branch <branch>] [--env <path-to-env>]
#
# On first run, provide --repo and --env.
# Subsequent runs pick up repo/branch from /opt/app/config.

set -euo pipefail

# ─── Config ────────────────────────────────────────────────────────────────────
BASE_DIR="${DEPLOY_BASE_DIR:-/opt/app}"
STATE_FILE="$BASE_DIR/.active"
CONFIG_FILE="$BASE_DIR/config"
ENV_SOURCE="$BASE_DIR/.env"
PORT_BLUE="${BLUE_HTTP_PORT:-18081}"
PORT_GREEN="${GREEN_HTTP_PORT:-18082}"
PORT_PGADMIN_BLUE="${BLUE_PGADMIN_PORT:-15050}"
PORT_PGADMIN_GREEN="${GREEN_PGADMIN_PORT:-15051}"
HEALTH_TIMEOUT=60     # seconds to wait for new stack to become healthy
CADDY_FILE="/etc/caddy/Caddyfile"
BASE_COMPOSE_FILE="docker-compose.deploy.yml"
APP_UID="$(id -u)"
APP_GID="$(id -g)"
TEST_DB_NAME="${DEPLOY_TEST_DB_NAME:-app_deploy_test}"

if docker compose version >/dev/null 2>&1; then
  COMPOSE_BIN=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_V1_VERSION="$(docker-compose version --short 2>/dev/null || true)"
  if [[ "$COMPOSE_V1_VERSION" =~ ^1\. ]]; then
    echo "ERROR: docker-compose v1 ($COMPOSE_V1_VERSION) is not supported for blue-green deploy on this host." >&2
    echo "Install Docker Compose v2 plugin and rerun." >&2
    echo "Ubuntu quick fix:" >&2
    echo "  sudo apt-get update && sudo apt-get install -y docker-compose-plugin" >&2
    exit 1
  fi
  COMPOSE_BIN=(docker-compose)
else
  echo "ERROR: Neither 'docker compose' nor 'docker-compose' is available." >&2
  exit 1
fi

# ─── Parse args ────────────────────────────────────────────────────────────────
REPO=""
BRANCH="main"
ENV_PATH=""

while [[ $# -gt 0 ]]; do
  case $1 in
    --repo)    REPO="$2";    shift 2 ;;
    --branch)  BRANCH="$2";  shift 2 ;;
    --env)     ENV_PATH="$2"; shift 2 ;;
    *) echo "Unknown argument: $1"; exit 1 ;;
  esac
done

# ─── Bootstrap dirs ────────────────────────────────────────────────────────────
mkdir -p "$BASE_DIR/blue" "$BASE_DIR/green"

# Load / write config
if [[ -f "$CONFIG_FILE" ]]; then
  source "$CONFIG_FILE"
fi
if [[ -n "$REPO" ]]; then
  echo "REPO=$REPO" > "$CONFIG_FILE"
  echo "BRANCH=$BRANCH" >> "$CONFIG_FILE"
fi
if [[ -z "${REPO:-}" ]]; then
  echo "ERROR: --repo is required on first run." >&2
  exit 1
fi
BRANCH="${BRANCH:-main}"

# Copy .env if supplied and it's not already the same file
if [[ -n "$ENV_PATH" && "$(realpath "$ENV_PATH")" != "$(realpath "$ENV_SOURCE")" ]]; then
  cp "$ENV_PATH" "$ENV_SOURCE"
fi
if [[ ! -f "$ENV_SOURCE" ]]; then
  echo "ERROR: $ENV_SOURCE not found. Provide --env on first run." >&2
  exit 1
fi

DB_USERNAME_VALUE="$(grep -E '^DB_USERNAME=' "$ENV_SOURCE" | tail -n1 | cut -d'=' -f2- || true)"
DB_PASSWORD_VALUE="$(grep -E '^DB_PASSWORD=' "$ENV_SOURCE" | tail -n1 | cut -d'=' -f2- || true)"
if [[ -z "$DB_USERNAME_VALUE" ]]; then
  DB_USERNAME_VALUE="app"
fi

CURRENT_APP_KEY="$(grep -E '^APP_KEY=' "$ENV_SOURCE" | head -n1 | cut -d'=' -f2- || true)"
if [[ -z "$CURRENT_APP_KEY" ]]; then
  GENERATED_APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  if grep -qE '^APP_KEY=' "$ENV_SOURCE"; then
    sed -i "s#^APP_KEY=.*#APP_KEY=${GENERATED_APP_KEY}#" "$ENV_SOURCE"
  else
    printf '\nAPP_KEY=%s\n' "$GENERATED_APP_KEY" >> "$ENV_SOURCE"
  fi
  echo "Generated APP_KEY in $ENV_SOURCE"
fi

# ─── Determine colors ──────────────────────────────────────────────────────────
ACTIVE=$(cat "$STATE_FILE" 2>/dev/null || echo "green")   # green = nothing running yet
if [[ "$ACTIVE" == "blue" ]]; then
  NEW="green"; NEW_PORT=$PORT_GREEN; NEW_PGADMIN_PORT=$PORT_PGADMIN_GREEN; OLD_PORT=$PORT_BLUE
else
  NEW="blue";  NEW_PORT=$PORT_BLUE;  NEW_PGADMIN_PORT=$PORT_PGADMIN_BLUE; OLD_PORT=$PORT_GREEN
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Deploying → $NEW  (old: $ACTIVE)"
echo "  Port $NEW_PORT will become active"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

NEW_DIR="$BASE_DIR/$NEW"

# ─── Pull latest code ──────────────────────────────────────────────────────────
echo ""
echo "[1/10] Pulling code into $NEW_DIR ..."
if [[ -d "$NEW_DIR/.git" ]]; then
  git -C "$NEW_DIR" fetch origin
  git -C "$NEW_DIR" reset --hard "origin/$BRANCH"
else
  rm -rf "$NEW_DIR"
  git clone --depth=1 --branch "$BRANCH" "$REPO" "$NEW_DIR"
fi

cp "$ENV_SOURCE" "$NEW_DIR/.env"

# ─── Build & start new stack ───────────────────────────────────────────────────
echo ""
echo "[2/10] Building and starting $NEW stack ..."
cd "$NEW_DIR"
if [[ ! -f "$BASE_COMPOSE_FILE" ]]; then
  echo "ERROR: $BASE_COMPOSE_FILE not found in $NEW_DIR." >&2
  exit 1
fi

APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" \
  -f "$BASE_COMPOSE_FILE" \
  -f "docker-compose.$NEW.yml" \
  up -d --build --remove-orphans

# ─── Wait for health ───────────────────────────────────────────────────────────
echo ""
echo "[3/10] Installing PHP dependencies ..."
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app composer install --no-dev --prefer-dist --optimize-autoloader

# ─── Wait for health ───────────────────────────────────────────────────────────
echo ""
echo "[4/10] Waiting for $NEW stack on port $NEW_PORT ..."
ELAPSED=0
until curl -sf "http://127.0.0.1:$NEW_PORT/api/health" > /dev/null 2>&1; do
  if [[ $ELAPSED -ge $HEALTH_TIMEOUT ]]; then
    echo "ERROR: Health check timed out after ${HEALTH_TIMEOUT}s. Aborting." >&2
    APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" logs --tail=50
    exit 1
  fi
  sleep 2
  ELAPSED=$((ELAPSED + 2))
done
echo "  ✓ $NEW stack is healthy"

# ─── Run migrations ────────────────────────────────────────────────────────────
echo ""
echo "[5/10] Running migrations ..."
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app php artisan migrate --force
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app php artisan optimize

# ─── Run tests on isolated DB ─────────────────────────────────────────────────
echo ""
echo "[6/10] Creating isolated test database ($TEST_DB_NAME) ..."
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T db sh -lc "psql -U \"$DB_USERNAME_VALUE\" -d postgres -v ON_ERROR_STOP=1 -c 'DROP DATABASE IF EXISTS \"$TEST_DB_NAME\";' -c 'CREATE DATABASE \"$TEST_DB_NAME\";'"

echo "[7/10] Running test suite against isolated database ..."
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app env \
  APP_ENV=testing \
  DB_CONNECTION=pgsql \
  DB_HOST=db \
  DB_PORT=5432 \
  DB_DATABASE="$TEST_DB_NAME" \
  DB_USERNAME="$DB_USERNAME_VALUE" \
  DB_PASSWORD="$DB_PASSWORD_VALUE" \
  php artisan test --testsuite=Unit --testsuite=Feature

echo "[8/10] Cleaning isolated test database ..."
APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T db sh -lc "psql -U \"$DB_USERNAME_VALUE\" -d postgres -v ON_ERROR_STOP=1 -c 'DROP DATABASE IF EXISTS \"$TEST_DB_NAME\";'"

# ─── Switch Caddy upstream ─────────────────────────────────────────────────────
echo ""
echo "[9/10] Switching Caddy to port $NEW_PORT ..."
CADDY_TMP_FILE="$(mktemp)"
cat > "$CADDY_TMP_FILE" <<EOF
{
    email admin@example.com
}

${DOMAIN:-localhost} {
    reverse_proxy 127.0.0.1:${NEW_PORT}
}

${PGADMIN_DOMAIN:-pgadmin.${DOMAIN:-localhost}} {
  reverse_proxy 127.0.0.1:${NEW_PGADMIN_PORT}
}
EOF

if [[ -w "$CADDY_FILE" ]] || [[ ! -e "$CADDY_FILE" && -w "$(dirname "$CADDY_FILE")" ]]; then
  install -m 644 "$CADDY_TMP_FILE" "$CADDY_FILE"
else
  sudo install -m 644 "$CADDY_TMP_FILE" "$CADDY_FILE"
fi

rm -f "$CADDY_TMP_FILE"

if command -v caddy >/dev/null 2>&1; then
  sudo caddy fmt --overwrite "$CADDY_FILE" >/dev/null 2>&1 || true
  sudo caddy validate --config "$CADDY_FILE" --adapter caddyfile
fi

if command -v systemctl >/dev/null 2>&1; then
  sudo systemctl reload caddy || sudo systemctl restart caddy
elif command -v caddy >/dev/null 2>&1; then
  sudo caddy reload --config "$CADDY_FILE" --adapter caddyfile
else
  echo "ERROR: Neither systemctl nor caddy CLI is available to reload Caddy." >&2
  exit 1
fi
echo "  ✓ Traffic now routed to $NEW (port $NEW_PORT)"

# ─── Tear down old stack ───────────────────────────────────────────────────────
echo ""
echo "[10/10] Stopping old $ACTIVE stack ..."
OLD_DIR="$BASE_DIR/$ACTIVE"
if [[ -d "$OLD_DIR" ]]; then
  APP_UID="$APP_UID" APP_GID="$APP_GID" COMPOSE_PROJECT_NAME="app_$ACTIVE" "${COMPOSE_BIN[@]}" \
    -f "$OLD_DIR/$BASE_COMPOSE_FILE" \
    -f "$OLD_DIR/docker-compose.$ACTIVE.yml" \
    down --remove-orphans || true
fi

# ─── Save new state ────────────────────────────────────────────────────────────
echo "$NEW" > "$STATE_FILE"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Deploy complete. Active: $NEW (port $NEW_PORT)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
