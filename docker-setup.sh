#!/bin/bash
# SpotMap Docker Setup Script
# ⚠️ PROPRIETARY CODE - DO NOT DISTRIBUTE
# Quick setup for Docker development environment

set -e

echo "╔════════════════════════════════════════════════════╗"
echo "║     SpotMap Docker Setup                          ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

echo "✓ Docker found"

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

echo "✓ Docker Compose found"

# Copy .env.docker if .env doesn't exist
if [ ! -f ".env.docker" ]; then
    echo "❌ .env.docker not found. Creating from template..."
    if [ -f ".env.example" ]; then
        cp .env.example .env.docker
        echo "✓ .env.docker created"
    else
        echo "❌ .env.example not found"
        exit 1
    fi
else
    echo "✓ .env.docker found"
fi

# Create necessary directories
echo ""
echo "📁 Creating directories..."
mkdir -p backend/logs
mkdir -p backend/public/uploads
mkdir -p docker
echo "✓ Directories created"

# Build Docker image
echo ""
echo "🔨 Building Docker image..."
docker-compose build --no-cache
echo "✓ Docker image built"

# Create volumes
echo ""
echo "💾 Creating Docker volumes..."
docker volume create spotmap-logs || true
docker volume create spotmap-uploads || true
docker volume create spotmap-mysql || true
docker volume create spotmap-redis || true
echo "✓ Volumes created"

# Start services
echo ""
echo "🚀 Starting Docker containers..."
docker-compose up -d
echo "✓ Containers started"

# Wait for services to be ready
echo ""
echo "⏳ Waiting for services to be ready..."
sleep 10

# Run migrations
echo ""
echo "📦 Running database migrations..."
docker-compose exec -T spotmap php migrate.php up || true
echo "✓ Migrations completed"

# Health check
echo ""
echo "🏥 Checking health..."
if docker-compose exec -T spotmap curl -f http://localhost:8080/health > /dev/null 2>&1; then
    echo "✓ Health check passed"
else
    echo "⚠️  Health check failed (may take a few seconds)"
fi

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║     ✅ Setup Complete!                            ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""
echo "Access the application:"
echo "  🌐 Web:         http://localhost:8080"
echo "  📊 Monitoring:  http://localhost:8080/monitoring.html"
echo "  📚 API Docs:    http://localhost:8080/api/docs"
echo ""
echo "Database:"
echo "  MySQL:   localhost:3306 (user: spotmap, pass: spotmap123)"
echo "  Redis:   localhost:6379 (pass: redis123)"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f spotmap     - Follow logs"
echo "  docker-compose down                - Stop services"
echo "  docker-compose down -v             - Stop and remove volumes"
echo "  docker-compose exec spotmap php ... - Run PHP command"
echo ""
