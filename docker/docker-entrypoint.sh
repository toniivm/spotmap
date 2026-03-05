#!/bin/sh
# Docker Entrypoint Script for SpotMap
# ⚠️ PROPRIETARY CODE - DO NOT DISTRIBUTE

set -e

echo "🚀 Starting SpotMap Container..."

# Wait for MySQL/PostgreSQL to be ready
if [ ! -z "$DB_HOST" ]; then
    echo "⏳ Waiting for database at $DB_HOST:${DB_PORT:-3306}..."
    while ! nc -z "$DB_HOST" "${DB_PORT:-3306}"; do
        sleep 1
    done
    echo "✅ Database is ready"
fi

# Create log directories if they don't exist
mkdir -p /app/logs
chmod 755 /app/logs

# Run migrations if needed
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "📦 Running database migrations..."
    php /app/migrate.php up || true
    echo "✅ Migrations completed"
fi

# Fix permissions for logs directory
if [ -d "/app/logs" ]; then
    chmod 755 /app/logs
fi

# Start the command
echo "✅ Container ready. Executing: $@"
exec "$@"
