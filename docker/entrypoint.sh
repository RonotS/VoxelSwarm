#!/bin/sh
set -e

echo "==> VoxelSwarm: Preparing persistent storage..."

# Ensure runtime dirs exist for PHP-FPM socket
mkdir -p /run/php /run/nginx

# Ensure volume directories exist
mkdir -p /data/logs
mkdir -p /data/instances
mkdir -p /data/template/voxelsite
mkdir -p /data/library

# Symlink storage directories to the persistent volume
# This keeps VoxelSwarm's expected paths but data lives on the volume
ln -sfn /data/logs      /app/storage/logs
ln -sfn /data/instances /app/storage/instances

# Symlink template and library directories
# Only link if the target isn't already a directory with content from the volume
if [ ! -L /app/template/voxelsite ] || [ "$(readlink /app/template/voxelsite)" != "/data/template/voxelsite" ]; then
    rm -rf /app/template/voxelsite
    ln -sfn /data/template/voxelsite /app/template/voxelsite
fi

if [ ! -L /app/library ] || [ "$(readlink /app/library)" != "/data/library" ]; then
    rm -rf /app/library
    ln -sfn /data/library /app/library
fi

# Copy bundled template to volume if not already there
if [ -d /app/template/voxelsite ] && [ ! -f /data/template/voxelsite/voxelsite-v1.27.0.zip ]; then
    echo "==> First deploy: copying voxelsite template to volume..."
    cp -a /app/template/voxelsite/* /data/template/voxelsite/ 2>/dev/null || true
fi

# Handle SQLite database
if [ ! -f /data/swarm.db ]; then
    echo "==> First deploy: creating empty database..."
    touch /data/swarm.db
fi
ln -sfn /data/swarm.db /app/storage/swarm.db

# Handle .env file
if [ ! -f /data/.env ]; then
    echo "==> First deploy: copying .env template..."
    if [ -f /app/.env.example ]; then
        cp /app/.env.example /data/.env
    else
        echo "APP_DEBUG=false" > /data/.env
        echo "APP_URL=https://localhost" >> /data/.env
    fi
fi
ln -sfn /data/.env /app/.env

# Fix permissions
chown -R www-data:www-data /data 2>/dev/null || true
chmod -R 755 /data

echo "==> VoxelSwarm: Storage ready. Starting services..."

exec "$@"
