FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    sqlite-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo_sqlite \
    mbstring \
    fileinfo \
    zip \
    curl \
    opcache

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Create required directories
RUN mkdir -p /data/logs /data/instances /data/template/voxelsite /data/library \
    && mkdir -p /app/storage /app/template /run/nginx

# Copy Docker config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# PHP-FPM config: listen on port 9000
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true
RUN echo "listen = 9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null || true

# PHP config optimizations
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini

# Expose Railway's expected port
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
