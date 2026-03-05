#!/bin/bash
# SpotMap Docker Stop Script
# ⚠️ PROPRIETARY CODE - DO NOT DISTRIBUTE
# Gracefully stop Docker containers

set -e

echo "╔════════════════════════════════════════════════════╗"
echo "║     SpotMap Docker Stop                           ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

echo "🔧 Using docker-compose.yml"

echo ""
echo "⏹️  Stopping containers..."
docker-compose down

echo ""
echo "✓ Containers stopped"

# Optional: Remove volumes
if [ "$1" = "-v" ] || [ "$1" = "--volumes" ]; then
    echo ""
    echo "🗑️  Removing volumes..."
    docker-compose down -v
    echo "✓ Volumes removed"
fi

# Optional: Show remaining images
if [ "$2" = "--clean" ]; then
    echo ""
    echo "🧹 Cleaning up unused Docker resources..."
    docker system prune -f
    echo "✓ Cleanup completed"
fi

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║     ✅ Stop Complete!                             ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""
echo "Usage:"
echo "  ./docker-stop.sh              - Stop containers"
echo "  ./docker-stop.sh -v           - Stop and remove volumes"
echo "  ./docker-stop.sh -v --clean   - Stop, remove volumes and cleanup"
echo ""
