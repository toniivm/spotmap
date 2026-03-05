# SpotMap - Dockerfile Optimizado con Multi-Stage Build
# ⚠️ CÓDIGO PROPIETARIO - NO DISTRIBUIR
# Copyright (c) 2025 Antonio Valero

###########
# Stage 1: Builder
###########
FROM php:8.2-fpm-alpine AS builder

LABEL maintainer="Antonio Valero"
LABEL description="SpotMap - Proprietary Climbing Spots Map"

# Install build dependencies
RUN apk add --no-cache \
    gcc g++ make pkgconfig \
    libpq-dev mysql-dev \
    openssl-dev \
    zlib-dev \
    curl-dev \
    git

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    json \
    curl \
    mbstring \
    opcache \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY backend /app
WORKDIR /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader 2>&1 || true

###########
# Stage 2: Runtime
###########
FROM php:8.2-fpm-alpine

LABEL maintainer="Antonio Valero"
LABEL version="1.2"
LABEL description="SpotMap Production Container"

# Install runtime dependencies
RUN apk add --no-cache \
# Install PHP extensions (production)
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    json \
    curl \
    mbstring \
    opcache \
    bcmath \
    gd

# Copy application from builder
COPY --from=builder /app /app

# Create non-root user
RUN addgroup -g 1000 spotmap && \
    adduser -D -u 1000 -G spotmap spotmap

# Create necessary directories
RUN mkdir -p /app/logs /app/public/uploads && \
    chown -R spotmap:spotmap /app

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set working directory
WORKDIR /app

# Change to spotmap user
USER spotmap

# Expose ports
EXPOSE 9000 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# Set environment
ENV APP_ENV=production
ENV DEBUG=false
ENV LOG_LEVEL=INFO

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configurar permisos de upload
RUN mkdir -p /var/www/html/backend/public/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

WORKDIR /var/www/html
COPY frontend ./frontend
COPY backend ./backend
COPY nginx.conf .
COPY docker-compose.yml .
COPY vercel.json .

# Copiar dependencias PHP generadas en builder (vendor)
COPY --from=builder /app/backend/vendor ./backend/vendor

# Eliminar archivos que no son necesarios en runtime dentro de la imagen
RUN rm -rf ./backend/tests || true

# Configurar .htaccess para SPA routing
RUN echo '<IfModule mod_rewrite.c>\n\
    RewriteEngine On\n\
    RewriteBase /\n\
    RewriteRule ^index\.html$ - [L]\n\
    RewriteCond %{REQUEST_FILENAME} !-f\n\
    RewriteCond %{REQUEST_FILENAME} !-d\n\
    RewriteRule . /index.html [L]\n\
</IfModule>' > /var/www/html/frontend/.htaccess

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/backend/public/index.php/api/status || exit 1

# Exponer puerto
EXPOSE 80

# Usuario no root (seguridad)
USER www-data

# Comando de inicio
CMD ["apache2-foreground"]
