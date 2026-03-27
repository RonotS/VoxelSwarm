FROM php:8.2-cli-alpine

WORKDIR /app

# Create the simplest possible PHP file
RUN echo '<?php echo "Hello from VoxelSwarm! Port: " . getenv("PORT");' > /app/index.php

EXPOSE 8080

# Use PORT env var, listen on both IPv4 and IPv6
CMD sh -c 'echo "Starting on port ${PORT:-8080}" && php -S 0.0.0.0:${PORT:-8080} -t /app'
