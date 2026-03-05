# SpotMap Production Deployment Guide

## 🚀 Deployment Seguro - Production Setup

Esta guía proporciona instrucciones paso a paso para desplegar SpotMap en producción de forma segura.

---

## 📋 Tabla de Contenidos
1. [Requisitos Previos](#requisitos-previos)
2. [Variables de Entorno](#variables-de-entorno)
3. [Configuración SSL/TLS](#configuración-ssltls)
4. [Apache Configuration](#apache-configuration)
5. [Nginx Configuration (Alternativa)](#nginx-configuration-alternativa)
6. [Security Headers](#security-headers)
7. [Verificación y Testing](#verificación-y-testing)
8. [Troubleshooting](#troubleshooting)

---

## Requisitos Previos

- Linux server (Ubuntu 20.04 LTS o superior recomendado)
- Apache 2.4+ o Nginx 1.20+
- PHP 8.2+
- OpenSSL 1.1.1+
- Root o acceso sudo
- Dominio configurado (ej: spotmap.local)

---

## Variables de Entorno

### `.env.production` Location
```
/var/spotmap/.env.production
```

### Variables Requeridas
```ini
# Supabase
VITE_SUPABASE_URL=https://your-project.supabase.co
VITE_SUPABASE_ANON_KEY=your_anon_key_here

# API
VITE_API_URL=https://api.spotmap.local
VITE_API_TIMEOUT=30000

# Security
VITE_ENABLE_SECURITY_GUARD=true
VITE_ENABLE_FINGERPRINTING=true
VITE_BLOCK_DEVTOOLS=true
BACKEND_CSRF_ENABLED=true
BACKEND_RATE_LIMIT=60
```

### Variables Opcionales
```ini
# Logging
VITE_LOG_ERRORS=true
VITE_SEND_ERROR_REPORTS=true
VITE_ERROR_REPORT_URL=https://api.spotmap.local/api/errors/report

# Features
VITE_CACHE_ENABLED=true
VITE_CACHE_DURATION=3600000
VITE_MAINTENANCE_MODE=false
```

---

## Configuración SSL/TLS

### Opción 1: Self-Signed Certificate (Desarrollo/Testing)

```bash
# Generar private key
openssl genrsa -out /etc/ssl/private/spotmap.key 2048

# Generar certificate (válido 365 días)
openssl req -new -x509 -key /etc/ssl/private/spotmap.key \
  -out /etc/ssl/certs/spotmap.crt -days 365 \
  -subj "/C=ES/ST=Madrid/L=Madrid/O=SpotMap/CN=spotmap.local"

# Permisos
chmod 600 /etc/ssl/private/spotmap.key
chmod 644 /etc/ssl/certs/spotmap.crt
```

### Opción 2: Let's Encrypt (Recomendado para Producción)

```bash
# Instalar certbot
apt install certbot python3-certbot-apache

# Generar certificado
certbot certonly --apache -d spotmap.local -d api.spotmap.local

# Auto-renewal
systemctl enable certbot.timer
systemctl start certbot.timer
```

---

## Apache Configuration

### 1. Habilitar módulos requeridos

```bash
a2enmod ssl
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod gzip
a2enmod ratelimit
```

### 2. Copiar configuración

```bash
cp apache-production.conf /etc/apache2/sites-available/spotmap-ssl.conf
```

### 3. Editar configuración

```bash
nano /etc/apache2/sites-available/spotmap-ssl.conf
```

Actualizar:
- `ServerName` → tu dominio
- `DocumentRoot` → ruta correcta
- `SSLCertificateFile` → ruta del cert
- `SSLCertificateKeyFile` → ruta de la key

### 4. Habilitar sitio

```bash
a2ensite spotmap-ssl
a2dissite 000-default
```

### 5. Probar y reiniciar

```bash
apache2ctl configtest
systemctl restart apache2
```

---

## Nginx Configuration (Alternativa)

### 1. Copiar configuración

```bash
cp nginx-production.conf /etc/nginx/sites-available/spotmap-ssl.conf
ln -s /etc/nginx/sites-available/spotmap-ssl.conf /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
```

### 2. Editar configuración

```bash
nano /etc/nginx/sites-available/spotmap-ssl.conf
```

Actualizar:
- `server_name` → tu dominio
- `ssl_certificate` → ruta del cert
- `ssl_certificate_key` → ruta de la key
- `root` → ruta correcta

### 3. Probar y reiniciar

```bash
nginx -t
systemctl restart nginx
```

---

## Security Headers

### Content Security Policy (CSP)

El archivo de configuración incluye CSP que:
- ✅ Bloquea inline scripts
- ✅ Permite solo scripts de origen propio
- ✅ Bloquea iframes de terceros
- ✅ Restringe carga de recursos

### Strict Transport Security (HSTS)

```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

- 1 año de validez
- Aplica a todos los subdominios
- Preload list compatible

### Otras Headers

| Header | Valor | Propósito |
|--------|-------|-----------|
| X-Content-Type-Options | nosniff | Previene MIME sniffing |
| X-Frame-Options | DENY | Bloquea clickjacking |
| X-XSS-Protection | 1; mode=block | Protección XSS |
| Referrer-Policy | strict-origin-when-cross-origin | Control de referer |
| Permissions-Policy | camera=(), microphone=() | Bloquea APIs sensibles |

---

## Verificación y Testing

### 1. Test HTTPS

```bash
# Verificar certificado
openssl s_client -connect spotmap.local:443

# Verificar headers de seguridad
curl -I https://spotmap.local
```

### 2. Test CSP

```bash
curl -I https://api.spotmap.local | grep -i content-security-policy
```

### 3. Test de rendimiento

```bash
# Verificar compresión gzip
curl -H "Accept-Encoding: gzip" -I https://spotmap.local

# Verificar caché
curl -I https://spotmap.local/static/app.js | grep -i cache-control
```

### 4. Test de seguridad online

- https://www.ssllabs.com/ssltest/
- https://securityheaders.com/
- https://csp-evaluator.withgoogle.com/

### 5. Verificar logs

```bash
# Apache
tail -f /var/log/apache2/spotmap-error.log
tail -f /var/log/apache2/spotmap-access.log

# Nginx
tail -f /var/log/nginx/spotmap-error.log
tail -f /var/log/nginx/spotmap-access.log
```

---

## Troubleshooting

### Error: Certificate not found

```bash
# Verificar ubicación
ls -la /etc/ssl/certs/spotmap.crt
ls -la /etc/ssl/private/spotmap.key

# Verificar permisos
chmod 644 /etc/ssl/certs/spotmap.crt
chmod 600 /etc/ssl/private/spotmap.key
```

### Error: Apache module not enabled

```bash
# Ver módulos disponibles
apache2ctl -M | grep ssl

# Habilitar módulo
a2enmod ssl_module
systemctl restart apache2
```

### CORS errors

Verificar en `/etc/apache2/sites-available/spotmap-ssl.conf`:
```apache
Header always set Access-Control-Allow-Origin "https://api.spotmap.local"
Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
```

### Rate limiting no funciona

Verificar que `mod_ratelimit` está habilitado:
```bash
apache2ctl -M | grep ratelimit
a2enmod ratelimit
```

### PHP errors

```bash
# Verificar error log
tail -f /var/log/php-error.log

# Verificar memoria
php -i | grep memory_limit
```

---

## 🔒 Checklist de Seguridad

- [ ] SSL/TLS habilitado (TLS 1.2+)
- [ ] HSTS header configurado
- [ ] CSP header aplicado
- [ ] X-Frame-Options = DENY
- [ ] HTTP redirige a HTTPS
- [ ] Gzip compression activo
- [ ] Archivos sensibles protegidos (.env, .git, config/)
- [ ] Permisos de archivos correctos (755 dirs, 644 files)
- [ ] Variables de entorno cargadas
- [ ] Rate limiting activo
- [ ] CORS restrictivo
- [ ] Logging habilitado
- [ ] Certificado válido y actualizado

---

## 📞 Soporte

Para problemas durante el deployment:

1. Revisar logs en `/var/log/apache2/` o `/var/log/nginx/`
2. Verificar archivo `/var/log/php-error.log`
3. Probar configuración: `apache2ctl configtest` o `nginx -t`
4. Consultar SECURITY.md para detalles adicionales

---

**⚠️ CONFIDENCIAL - NO COMPARTIR**

Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
