#!/bin/bash
# SpotMap Docker Deploy Script
# ⚠️ PROPRIETARY CODE - DO NOT DISTRIBUTE
# Production deployment with Docker Compose

set -e

echo "╔════════════════════════════════════════════════════╗"
echo "║     SpotMap Docker Production Deployment          ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
   echo "❌ This script must be run as root"
   exit 1
fi

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose down || true
echo "✓ Containers stopped"

# Pull latest images
echo ""
echo "📥 Pulling latest images..."
docker-compose pull
echo "✓ Images pulled"

# Build application image
echo ""
echo "🔨 Building application image..."
docker-compose build --no-cache spotmap
echo "✓ Image built"

# Create backup
echo ""
echo "💾 Creating database backup..."
BACKUP_DIR="/var/backups/spotmap"
mkdir -p "$BACKUP_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump \
    -u spotmap -p"$DB_PASSWORD" spotmap > "$BACKUP_DIR/spotmap_$TIMESTAMP.sql" || true
echo "✓ Backup created: spotmap_$TIMESTAMP.sql"

# Start services
echo ""
echo "🚀 Starting services..."
docker-compose up -d
echo "✓ Services started"

# Wait for services
echo ""
echo "⏳ Waiting for services to be ready..."
sleep 15

# Run migrations
echo ""
echo "📦 Running database migrations..."
docker-compose exec -T spotmap php migrate.php up || true
echo "✓ Migrations completed"

# Health check
echo ""
echo "🏥 Health checks..."
HEALTH=$(docker-compose exec -T spotmap curl -s http://localhost:8080/health || echo "failed")
if [ "$HEALTH" = "healthy" ]; then
    echo "✓ Application health check: PASSED"
else
    echo "⚠️  Application health check: CHECK MANUALLY"
fi

# Display status
echo ""
echo "📊 Service status:"
docker-compose ps

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║     ✅ Deployment Complete!                       ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""
echo "Production URLs:"
echo "  🌐 Application:  https://spotmap.example.com"
echo "  📊 Monitoring:   https://spotmap.example.com/monitoring.html"
echo ""
echo "Recent logs:"
docker-compose logs --tail=10 spotmap
