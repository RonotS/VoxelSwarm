#!/bin/sh
set -e

echo "==> VoxelSwarm: Running entrypoint..."
/entrypoint.sh

echo "==> VoxelSwarm: Starting services (nginx + php-fpm)..."
exec supervisord -c /etc/supervisord.conf
