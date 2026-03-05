# 🐳 SpotMap Docker & Orquestación - Guía Completa

## 📋 Índice

1. [Introducción](#introducción)
2. [Requisitos](#requisitos)
3. [Instalación Rápida](#instalación-rápida)
4. [Uso Diario](#uso-diario)
5. [Configuración](#configuración)
6. [Producción](#producción)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Introducción

Docker permite ejecutar SpotMap en cualquier máquina sin configuración manual. Incluye:

- **PHP 8.2-FPM** - Application server
- **Nginx** - Web server
- **MySQL 8.0** - Base de datos
- **Redis 7** - Cache
- **PostgreSQL 15** - Optional DB

Todo pre-configurado y listo para producción.

---

## 📦 Requisitos

### Mínimos
- **Docker:** 20.10+
- **Docker Compose:** 2.0+
- **Espacio disco:** 5GB

### Recomendados
- **RAM:** 4GB minimum, 8GB ideal
- **CPU:** 2 cores minimum, 4 cores ideal

### Instalación

```bash
# macOS (Homebrew)
brew install docker docker-compose

# Ubuntu/Debian
sudo apt-get install docker.io docker-compose

# Windows
# Descargar Docker Desktop desde https://www.docker.com/products/docker-desktop

# Verificar instalación
docker --version
docker-compose --version
```

---

## 🚀 Instalación Rápida

### Opción 1: Setup Automático (Recomendado)

```bash
# Clonar repositorio
git clone <repo-url>
cd spotmap

# Ejecutar setup script
chmod +x docker-setup.sh
./docker-setup.sh
```

Listo! Acceder a http://localhost:8080

### Opción 2: Manual

```bash
# Copiar variables de entorno
cp .env.docker .env

# Crear directorios necesarios
mkdir -p backend/logs backend/public/uploads

# Iniciar servicios
docker-compose up -d

# Ejecutar migraciones
docker-compose exec spotmap php migrate.php up
```

---

## 📊 Uso Diario

### Ver estado de servicios

```bash
docker-compose ps
```

Salida esperada:
```
NAME                COMMAND                 STATE       PORTS
spotmap-app         "/docker-entrypoint.sh" Up (healthy)
spotmap-mysql       "docker-entrypoint.sh"  Up (healthy)
spotmap-redis       "redis-server..."       Up (healthy)
```

### Ver logs

```bash
# Todos los servicios
docker-compose logs -f

# Servicio específico
docker-compose logs -f spotmap
docker-compose logs -f mysql
docker-compose logs -f redis

# Últimas 50 líneas
docker-compose logs --tail=50 spotmap
```

### Ejecutar comandos PHP

```bash
# CLI interactive
docker-compose exec spotmap bash

# Comando simple
docker-compose exec spotmap php -v
docker-compose exec spotmap php migrate.php status

# Composer
docker-compose exec spotmap composer install
```

### Acceso a base de datos

```bash
# MySQL
docker-compose exec mysql mysql -u spotmap -p spotmap

# Redis
docker-compose exec redis redis-cli

# PostgreSQL (si está habilitado)
docker-compose exec postgres psql -U spotmap -d spotmap
```

### Detener servicios

```bash
# Detener sin eliminar volúmenes (preserva datos)
docker-compose down

# Detener y eliminar volúmenes (borra datos)
docker-compose down -v

# Detener un servicio específico
docker-compose stop spotmap
```

### Reiniciar servicios

```bash
# Reiniciar todo
docker-compose restart

# Reiniciar servicio específico
docker-compose restart spotmap
```

---

## ⚙️ Configuración

### Variables de Entorno (.env.docker)

```ini
# Desarrollo
APP_ENV=development
DEBUG=true
LOG_LEVEL=DEBUG

# Base de datos
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=spotmap
DB_USERNAME=spotmap
DB_PASSWORD=spotmap123

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=redis123

# Seguridad
ADMIN_API_TOKEN=dev_token_change_in_prod
```

### Cambiar puerto de aplicación

```bash
# En .env.docker
APP_PORT=9000

# O pasar como variable
docker-compose up -e APP_PORT=9000 -d
```

### Usar PostgreSQL en lugar de MySQL

```bash
# Iniciar con PostgreSQL
docker-compose --profile postgres up -d

# Conectar
docker-compose exec postgres psql -U spotmap -d spotmap
```

### Añadir nuevos servicios

Editar `docker-compose.yml`:

```yaml
services:
  # ... existing services
  
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.0.0
    environment:
      - discovery.type=single-node
    ports:
      - "9200:9200"
    networks:
      - spotmap-network
```

---

## 🏭 Producción

### Usar docker-compose.prod.yml

```bash
# Configurar variables de producción
cp .env.docker .env.production
# Editar .env.production con valores seguros

# Iniciar con archivo de producción
docker-compose -f docker-compose.prod.yml up -d

# O usar script de despliegue
chmod +x docker-deploy.sh
./docker-deploy.sh
```

### Cambios de producción

```yaml
# docker-compose.prod.yml
services:
  spotmap:
    restart: always
    mem_limit: 1g
    cpus: '1.5'
```

### Backups automáticos

```bash
# Crear backup manual
docker-compose exec mysql mysqldump -u spotmap -p spotmap > backup.sql

# Restaurar backup
docker exec spotmap-mysql mysql -u spotmap -p spotmap < backup.sql
```

### Monitoreo

```bash
# Health check
docker-compose exec spotmap curl http://localhost:8080/health

# Métricas
docker stats

# Log centralizado
docker-compose logs --follow spotmap
```

### Escalado

```bash
# Aumentar límites de recuros
docker update --memory=2g spotmap-app

# Usar múltiples instancias (requiere load balancer)
docker-compose up -d --scale spotmap=3
```

---

## 🔒 Seguridad

### Usuarios no-root

Los containers corren como usuario `spotmap` (UID 1000) por seguridad.

```dockerfile
USER spotmap
```

### Volúmenes read-only

Configuración protegida:
```yaml
volumes:
  - .env.docker:/app/.env:ro
```

### Red aislada

Services comunican por red interna `spotmap-network`:
```bash
docker network inspect spotmap-network
```

### Secretos

Para producción, usar Docker Secrets:

```bash
# Crear secreto
echo "secure_password" | docker secret create db_password -

# Usar en compose
secrets:
  db_password:
    external: true
```

---

## 🔧 Troubleshooting

### Container no inicia

```bash
# Ver logs completos
docker-compose logs spotmap

# Reintentar con verbosidad
docker-compose up spotmap
```

### Base de datos no responde

```bash
# Health check
docker-compose exec mysql mysqladmin ping

# Reconectar
docker-compose restart mysql
docker-compose restart spotmap
```

### Permisos denegados

```bash
# Resetear permisos de volúmenes
sudo chown -R 1000:1000 backend/logs backend/public/uploads

# Recrear volúmenes
docker-compose down -v
docker-compose up -d
```

### Puertos en uso

```bash
# Cambiar puerto en .env.docker
APP_PORT=9000

# O matar proceso ocupando puerto
sudo lsof -i :8080
sudo kill -9 <PID>
```

### Limite de memoria

```bash
# Aumentar en docker-compose.yml
services:
  spotmap:
    mem_limit: 1g  # 1GB
    memswap_limit: 2g  # Con swap
```

---

## 📊 Monitoreo & Logs

### Dashboard de monitoreo

```
http://localhost:8080/monitoring.html
```

### Logs centralizados

```bash
# Todo
docker-compose logs -f

# Últimos 100 en tiempo real
docker-compose logs -f --tail=100

# JSON (para parsing)
docker-compose logs -f --format=json
```

### Métricas de Docker

```bash
# CPU y memoria en tiempo real
docker stats

# Espacio en disco
docker system df

# Eventos
docker events
```

---

## 📚 Comandos Útiles

```bash
# Limpiar todo
docker-compose down -v
docker system prune -a

# Actualizar imagen
docker-compose pull
docker-compose up -d

# Ver configuración
docker-compose config

# Validar sintaxis
docker-compose config --quiet

# Escalar servicios (experimental)
docker-compose up -d --scale spotmap=2

# Build sin cache
docker-compose build --no-cache

# Ejecutar test
docker-compose exec spotmap ./run-tests.ps1

# Shell interactivo
docker-compose exec spotmap /bin/bash
```

---

## 🎓 Ejemplos Prácticos

### Desarrollo local con reloading automático

```bash
# Los volúmenes están montados para hot-reload
docker-compose up -d
# Editar archivos locales y ver cambios en http://localhost:8080
```

### Testing

```bash
# Ejecutar tests en container
docker-compose exec spotmap ./run-tests.ps1

# Con coverage
docker-compose exec spotmap phpunit --coverage-html coverage/
```

### Backup y recuperación

```bash
# Backup completo
docker-compose exec mysql mysqldump -u spotmap -p spotmap > spotmap_$(date +%Y%m%d).sql

# Backup de volúmenes
tar -czf volumes_backup.tar.gz backend/logs backend/public/uploads

# Recuperar
gunzip < spotmap_20250101.sql | docker-compose exec -T mysql mysql -u spotmap -p spotmap
```

---

## 🚀 CI/CD Integration

### GitHub Actions

Ver `.github/workflows/docker-ci.yml` para pipeline automatizado.

### GitLab CI

```yaml
docker_build:
  script:
    - docker-compose build
    - docker-compose run spotmap ./run-tests.ps1
```

---

## 📖 Documentación Adicional

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PHP-FPM Best Practices](https://www.php.net/manual/en/install.fpm.php)
- [Nginx Documentation](https://nginx.org/en/docs/)

---

**⚠️ CONFIDENCIAL - NO COMPARTIR**

Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
