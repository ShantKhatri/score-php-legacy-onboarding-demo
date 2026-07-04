#!/usr/bin/env bash
# Entrypoint shim: renders config.php from env vars injected by score-compose,
# then hands off to apache2-foreground.
set -euo pipefail

: "${DB_HOST:?DB_HOST not set — is this container running under score-compose?}"
: "${DB_PORT:?DB_PORT not set}"
: "${DB_NAME:?DB_NAME not set}"
: "${DB_USER:?DB_USER not set}"
: "${DB_PASSWORD:?DB_PASSWORD not set}"

TEMPLATE=/app-config-template/config.php.tmpl
TARGET=/var/www/html/config.php

echo "[shim] Rendering ${TARGET} from template..."

sed \
  -e "s#__DB_HOST__#${DB_HOST}#g" \
  -e "s#__DB_PORT__#${DB_PORT}#g" \
  -e "s#__DB_NAME__#${DB_NAME}#g" \
  -e "s#__DB_USER__#${DB_USER}#g" \
  -e "s#__DB_PASSWORD__#${DB_PASSWORD}#g" \
  "$TEMPLATE" > "$TARGET"

chown www-data:www-data "$TARGET"
chmod 640 "$TARGET"

echo "[shim] config.php rendered. Handing off to apache2-foreground."

exec apache2-foreground
