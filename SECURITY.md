# 🔐 Guía de Seguridad - SpotMap (CONFIDENCIAL)

## ⚠️ ADVERTENCIA CRÍTICA

Este documento contiene información sensible sobre la arquitectura de seguridad de SpotMap.  
**NO compartir públicamente. Solo para uso interno del desarrollador.**

---

## 🛡️ Sistema de Protección Implementado

### 1. **Ofuscación de Código Frontend**

**Archivos protegidos**:
- `api.js` - Cliente HTTP
- `auth.js` - Sistema de autenticación
- `supabaseClient.js` - Integración Supabase
- `config.js` - Configuración sensible
- `spots.js` - Lógica de negocio
- `map.js` - Funcionalidad de mapa
- `cache.js` - Sistema de caché

**Nivel de protección**: AGRESIVO
- Control flow flattening (100%)
- Dead code injection (50%)
- String array encoding (RC4)
- Self-defending code
- Debug protection
- Identificadores hexadecimales

**Ejecución**:
```bash
cd frontend
node obfuscate.cjs
# Salida: frontend/js-obfuscated/
```

---

### 2. **Encriptación de Credenciales**

**Estado actual**: Integrado en configuración de entorno y backend

**Protecciones**:
- Variables de entorno para keys de Supabase
- Verificación de integridad con checksum
- Watermark único por sesión (anti-scraping)
- Detección de debugging (devtools)
- Deshabilitación de clic derecho en producción
- Bloqueo de atajos F12, Ctrl+Shift+I, Ctrl+U

**Implementación**:
- Variables de entorno en backend (`SUPABASE_URL`, `SUPABASE_*_KEY`, `DB_*`)
- Configuración dinámica de frontend mediante `frontend/js/config.js`
- Cabeceras y políticas de seguridad aplicadas en backend (`Security.php`)

---

### 3. **Sistema Anti-Scraping + Watermarking**

**Estado actual**: Endurecimiento centralizado en backend

**Características**:

#### Honeypots
- Campo invisible en formularios (detecta bots)
- Link trampa para crawlers
- Flagging automático de actividad sospechosa

#### Detección de Bots
- Análisis de movimientos de ratón
- Contador de clicks
- Medición de scrolls
- Velocidad de navegación

#### Fingerprinting
- Canvas fingerprinting único por usuario
- Tracking de UserAgent + pantalla
- Hash único no reversible

#### Rate Limiting Cliente
- Máximo 50 requests por minuto
- Bloqueo automático tras 3 infracciones
- Reporte al backend

#### Watermarking DOM
- Todos los elementos `.spot` llevan fingerprint
- Detección de scraping masivo
- MutationObserver para inyección dinámica

**Bloqueo automático**:
- Rate limiting por IP en backend
- Sanitización y validaciones en entrada/salida
- Control de origen/CORS y headers de seguridad

---

### 4. **Hardening Backend PHP**

**Archivo**: `backend/src/SecurityHardening.php`

#### Protección CSRF
```php
$token = SecurityHardening::generateCSRFToken();
SecurityHardening::validateCSRFToken($token);
```

#### Rate Limiting Agresivo
- 60 requests/minuto por IP
- Bloqueo automático de IPs sospechosas
- Persistencia en `config/blocked_ips.txt`

#### Sanitización Avanzada
```php
$safe = SecurityHardening::sanitizeInput($input, 'string');
```

**Tipos soportados**: string, email, url, int, float, sql

**Patrones bloqueados**:
- `<script>` tags
- `javascript:` URLs
- `eval()` functions
- SQL injection (UNION, DROP, INSERT, UPDATE, DELETE)

#### Encriptación AES-256-CBC
```php
$encrypted = SecurityHardening::encrypt($data, $key);
$decrypted = SecurityHardening::decrypt($encrypted, $key);
```

#### Detección IP Real
- Soporte para Cloudflare (`CF_CONNECTING_IP`)
- X-Forwarded-For
- X-Real-IP
- Validación FILTER_VALIDATE_IP

#### Headers de Seguridad Avanzados
- CSP estricto
- X-XSS-Protection
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- HSTS (Strict-Transport-Security)
- Referrer-Policy
- Permissions-Policy

#### Watermark de Copyright
```
X-SpotMap-Protected: true
X-Copyright: (c) 2025 Antonio Valero. Todos los derechos reservados.
```

---

### 5. **Licencia Propietaria**

**Archivo**: `LICENSE`

**Restricciones legales**:
1. ❌ PROHIBIDA LA COPIA
2. ❌ PROHIBIDA LA MODIFICACIÓN
3. ❌ PROHIBIDA LA DISTRIBUCIÓN
4. ❌ PROHIBIDA LA INGENIERÍA INVERSA
5. ⚠️ USO RESTRINGIDO (solo portfolio personal)

**Jurisdicción**: España (Madrid)  
**Protección**: Leyes de propiedad intelectual españolas e internacionales

---

### 6. **Build para Producción**

**Script**: `frontend/build-production.js`

**Proceso**:
1. Ofuscación de código crítico
2. Copia de archivos HTML/CSS
3. Generación de `production/` con código protegido
4. Configuración de variables de entorno

**Ejecución**:
```bash
cd frontend
node build-production.js
```

**Salida**: `frontend/production/`

---

## 🚀 Despliegue Seguro

### Checklist Pre-Deploy

- [ ] Ejecutar `node obfuscate.js`
- [ ] Ejecutar `node build-production.js`
- [ ] Verificar que archivos de `production/` están ofuscados
- [ ] Configurar variables de entorno en servidor:
  - `SUPABASE_URL`
  - `SUPABASE_ANON_KEY`
  - `PHP_ENCRYPTION_KEY`
- [ ] Activar HTTPS con certificado SSL válido
- [ ] Configurar firewall para bloquear IPs sospechosas
- [ ] Activar logs de seguridad en backend
- [ ] Verificar que `.gitignore` oculta código fuente original
- [ ] Subir SOLO carpeta `production/` al servidor
- [ ] NO subir carpetas `tests/`, `js/` originales
- [ ] Revisar que LICENSE está presente

### Estructura de Deploy

```
servidor/
├── backend/
│   ├── public/
│   │   └── api.php (con SecurityHardening activado)
│   └── src/
│       ├── SecurityHardening.php
│       └── ...
├── frontend/
│   ├── index.html
│   └── js/ (código OFUSCADO desde js-obfuscated/)
└── config/
    └── blocked_ips.txt (vacío inicialmente)
```

---

## 🔍 Monitoreo de Seguridad

### Logs a Revisar

**Backend**:
```
backend/logs/app.log
```

Buscar:
- `[SECURITY] Suspicious activity detected`
- `[SECURITY] IP bloqueada permanentemente`
- `Rate limit exceeded`
- `injection_attempt`

**IPs Bloqueadas**:
```
config/blocked_ips.txt
```

Cada línea = 1 IP bloqueada permanentemente

### Desbloquear IP Manualmente

```bash
# Editar archivo
nano config/blocked_ips.txt

# Eliminar la línea con la IP
# Guardar y reiniciar servidor
```

---

## ⚠️ Qué NO Hacer

❌ **NO subir a GitHub público**:
- Código fuente original (`frontend/js/*.js` sin ofuscar)
- Tests (`backend/tests/`, `frontend/tests/`)
- Documentación interna (`TESTING.md`, `RESUMEN_MEJORAS.md`)
- Archivos `.env`
- `config/blocked_ips.txt`

❌ **NO deshabilitar**:
- Rate limiting
- CSRF protection
- Sanitización de inputs
- Headers de seguridad

❌ **NO compartir**:
- Keys de Supabase
- Algoritmos de fingerprinting
- Lógica de honeypots
- Este documento (`SECURITY.md`)

---

## 🆘 En Caso de Brecha de Seguridad

1. **Acción inmediata**:
   ```bash
   # Bloquear todas las peticiones (modo mantenimiento)
   touch backend/public/.maintenance
   ```

2. **Revisar logs**:
   ```bash
   tail -100 backend/logs/app.log
   grep "SECURITY" backend/logs/app.log
   ```

3. **Rotar credenciales**:
   - Regenerar keys de Supabase
   - Cambiar `PHP_ENCRYPTION_KEY`
   - Actualizar variables de entorno

4. **Limpiar IPs bloqueadas**:
   ```bash
   > config/blocked_ips.txt
   ```

5. **Re-ofuscar código**:
   ```bash
   cd frontend
   node obfuscate.js
   node build-production.js
   ```

6. **Desplegar nueva versión**

---

## 📞 Contacto de Emergencia

**Desarrollador**: Antonio Valero  
**Email**: antonio.valero@spotmap.com  
**GitHub**: @antonio-valero (privado)

---

**Última actualización**: 9 de diciembre de 2025  
**Versión de seguridad**: 1.0  
**Clasificación**: CONFIDENCIAL
