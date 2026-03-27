FROM php:8.2-cli-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    curl \
    sqlite-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo_sqlite \
    mbstring \
    fileinfo \
    zip \
    opcache

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Create required directories
RUN mkdir -p /data/logs /data/instances /data/template/voxelsite /data/library \
    && mkdir -p /app/storage /app/template

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create start script
RUN printf '#!/bin/sh\nset -e\n/entrypoint.sh\necho "==> Starting PHP built-in server on port 8080..."\nexec php -S 0.0.0.0:8080 -t /app /app/index.php\n' > /start.sh && chmod +x /start.sh

# PHP config
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "display_errors=On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_log=/dev/stderr" >> /usr/local/etc/php/conf.d/errors.ini

EXPOSE 8080

CMD ["/start.sh"]
