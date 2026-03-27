#!/bin/sh
set -e

echo "========================================"
echo "==> VoxelSwarm Diagnostic Start Script"
echo "========================================"
echo "==> Date: $(date)"
echo "==> Whoami: $(whoami)"
echo "==> PWD: $(pwd)"
echo "==> PORT env: ${PORT:-not set}"
echo "==> RAILWAY_ENVIRONMENT: ${RAILWAY_ENVIRONMENT:-not set}"

# Use Railway's PORT or default to 8080
APP_PORT="${PORT:-8080}"
echo "==> Will listen on port: $APP_PORT"

# Run entrypoint for storage setup
echo "==> Running entrypoint..."
/entrypoint.sh
echo "==> Entrypoint complete."

# Diagnostics: check critical files
echo "==> Checking critical files..."
echo "  index.php exists: $(test -f /app/index.php && echo YES || echo NO)"
echo "  healthz.php exists: $(test -f /app/healthz.php && echo YES || echo NO)"
echo "  vendor/autoload.php exists: $(test -f /app/vendor/autoload.php && echo YES || echo NO)"
echo "  src/bootstrap.php exists: $(test -f /app/src/bootstrap.php && echo YES || echo NO)"
echo "  .env exists: $(test -f /app/.env && echo YES || echo NO)"
echo "  .env is symlink: $(test -L /app/.env && echo YES || echo NO)"
echo "  storage dir exists: $(test -d /app/storage && echo YES || echo NO)"
echo "  storage/swarm.db exists: $(test -f /app/storage/swarm.db && echo YES || echo NO)"
echo "  /data dir exists: $(test -d /data && echo YES || echo NO)"
echo "  VERSION file: $(cat /app/VERSION 2>/dev/null || echo 'MISSING')"

# Diagnostics: check PHP
echo "==> PHP version:"
php -v
echo "==> PHP extensions:"
php -m
echo "==> PHP config test (syntax check index.php):"
php -l /app/index.php 2>&1 || echo "SYNTAX ERROR!"
echo "==> PHP config test (syntax check bootstrap.php):"
php -l /app/src/bootstrap.php 2>&1 || echo "SYNTAX ERROR!"

# Quick PHP test
echo "==> Quick PHP execution test:"
php -r "echo 'PHP works OK' . PHP_EOL;" 2>&1

echo "========================================"
echo "==> Starting PHP built-in server on 0.0.0.0:$APP_PORT"
echo "========================================"

exec php -S "0.0.0.0:$APP_PORT" -t /app /app/index.php 2>&1
