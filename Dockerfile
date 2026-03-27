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

# Copy scripts
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/start.sh /start.sh
RUN chmod +x /entrypoint.sh /start.sh

# PHP config - errors visible
RUN echo "display_errors=On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_log=/dev/stderr" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 8080

CMD ["/start.sh"]
