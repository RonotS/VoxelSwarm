FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    curl-dev \
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
    && mkdir -p /app/storage /app/template /run/nginx /run/php

# Copy Docker config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/start.sh /start.sh
RUN chmod +x /entrypoint.sh /start.sh

# PHP-FPM config: use Unix socket for reliable nginx<->fpm communication
RUN echo '[global]' > /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'daemonize = no' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'error_log = /dev/stderr' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo '[www]' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'listen = /run/php/php-fpm.sock' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'listen.mode = 0666' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'user = root' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'group = root' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'catch_workers_output = yes' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'php_flag[display_errors] = on' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'php_admin_value[error_log] = /dev/stderr' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo 'php_admin_flag[log_errors] = on' >> /usr/local/etc/php-fpm.d/zz-docker.conf

# PHP config optimizations
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 8080

CMD ["/start.sh"]
