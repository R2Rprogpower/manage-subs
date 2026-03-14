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
PORT_BLUE=8081
PORT_GREEN=8082
HEALTH_TIMEOUT=60     # seconds to wait for new stack to become healthy
CADDY_FILE="/etc/caddy/Caddyfile"
BASE_COMPOSE_FILE="docker-compose.deploy.yml"

if docker compose version >/dev/null 2>&1; then
  COMPOSE_BIN=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
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

# ─── Determine colors ──────────────────────────────────────────────────────────
ACTIVE=$(cat "$STATE_FILE" 2>/dev/null || echo "green")   # green = nothing running yet
if [[ "$ACTIVE" == "blue" ]]; then
  NEW="green"; NEW_PORT=$PORT_GREEN; OLD_PORT=$PORT_BLUE
else
  NEW="blue";  NEW_PORT=$PORT_BLUE;  OLD_PORT=$PORT_GREEN
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Deploying → $NEW  (old: $ACTIVE)"
echo "  Port $NEW_PORT will become active"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

NEW_DIR="$BASE_DIR/$NEW"

# ─── Pull latest code ──────────────────────────────────────────────────────────
echo ""
echo "[1/6] Pulling code into $NEW_DIR ..."
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
echo "[2/6] Building and starting $NEW stack ..."
cd "$NEW_DIR"
if [[ ! -f "$BASE_COMPOSE_FILE" ]]; then
  echo "ERROR: $BASE_COMPOSE_FILE not found in $NEW_DIR." >&2
  exit 1
fi

COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" \
  -f "$BASE_COMPOSE_FILE" \
  -f "docker-compose.$NEW.yml" \
  up -d --build --remove-orphans

# ─── Wait for health ───────────────────────────────────────────────────────────
echo ""
echo "[3/6] Waiting for $NEW stack on port $NEW_PORT ..."
ELAPSED=0
until curl -sf "http://127.0.0.1:$NEW_PORT/api/health" > /dev/null 2>&1; do
  if [[ $ELAPSED -ge $HEALTH_TIMEOUT ]]; then
    echo "ERROR: Health check timed out after ${HEALTH_TIMEOUT}s. Aborting." >&2
    COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" logs --tail=50
    exit 1
  fi
  sleep 2
  ELAPSED=$((ELAPSED + 2))
done
echo "  ✓ $NEW stack is healthy"

# ─── Run migrations ────────────────────────────────────────────────────────────
echo ""
echo "[4/6] Running migrations ..."
COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app php artisan migrate --force
COMPOSE_PROJECT_NAME="app_$NEW" "${COMPOSE_BIN[@]}" exec -T app php artisan optimize

# ─── Switch Caddy upstream ─────────────────────────────────────────────────────
echo ""
echo "[5/6] Switching Caddy to port $NEW_PORT ..."
cat > "$CADDY_FILE" <<EOF
{
    email admin@example.com
}

${DOMAIN:-localhost} {
    reverse_proxy 127.0.0.1:${NEW_PORT}
}
EOF
systemctl reload caddy || caddy reload --config "$CADDY_FILE"
echo "  ✓ Traffic now routed to $NEW (port $NEW_PORT)"

# ─── Tear down old stack ───────────────────────────────────────────────────────
echo ""
echo "[6/6] Stopping old $ACTIVE stack ..."
OLD_DIR="$BASE_DIR/$ACTIVE"
if [[ -d "$OLD_DIR" ]]; then
  COMPOSE_PROJECT_NAME="app_$ACTIVE" "${COMPOSE_BIN[@]}" \
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
