# Sistema de Auditoría para Moderación - SpotMap

## 📋 Resumen

Sistema completo de **seguimiento inmutable** para todas las acciones de moderación en SpotMap. Proporciona:

- ✅ **Trazabilidad completa** de acciones de moderadores
- ✅ **Historial forense** para investigaciones
- ✅ **Cumplimiento normativo** (GDPR, accountability)
- ✅ **Estadísticas** de actividad de moderación
- ✅ **API de consulta** con filtros avanzados

---

## 🗄️ Base de Datos

### Tabla: `moderation_audit_log`

```sql
CREATE TABLE moderation_audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  moderator_id CHAR(36) NOT NULL,           -- UUID del moderador
  action VARCHAR(50) NOT NULL,               -- approve_spot, reject_spot, etc.
  target_type VARCHAR(50) NOT NULL,          -- spot, comment, user, report
  target_id VARCHAR(100) NOT NULL,           -- ID del recurso modificado
  old_value TEXT,                            -- Estado previo (JSON)
  new_value TEXT,                            -- Estado nuevo (JSON)
  reason TEXT,                               -- Justificación del moderador
  metadata JSON,                             -- Contexto: IP, user_agent, etc.
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_moderator_id (moderator_id),
  INDEX idx_action (action),
  INDEX idx_target (target_type, target_id),
  INDEX idx_created_at (created_at)
);
```

### Tipos de Acciones Registradas

| Action | Target Type | Descripción |
|--------|-------------|-------------|
| `approve_spot` | `spot` | Aprobación de spot pendiente |
| `reject_spot` | `spot` | Rechazo de spot con razón |
| `delete_comment` | `comment` | Eliminación de comentario |
| `ban_user` | `user` | Suspensión de cuenta |
| `unban_user` | `user` | Reactivación de cuenta |
| `feature_spot` | `spot` | Marcar spot como destacado |
| `hide_spot` | `spot` | Ocultar spot sin eliminar |
| `resolve_report` | `report` | Resolver reporte de usuario |

---

## 🛠️ API de Auditoría

### 1. Listar Logs de Auditoría (Admin-only)

**Endpoint:** `GET /api?action=audit`

**Autenticación:** Bearer token (role: admin)

**Query Parameters:**
- `moderator_id` - Filtrar por moderador específico
- `action` - Filtrar por tipo de acción (`approve_spot`, etc.)
- `target_type` - Filtrar por tipo de recurso (`spot`, `comment`, etc.)
- `target_id` - Filtrar por ID de recurso específico
- `date_from` - Fecha inicio (YYYY-MM-DD)
- `date_to` - Fecha fin (YYYY-MM-DD)
- `limit` - Registros por página (default: 50, max: 200)
- `offset` - Offset para paginación (default: 0)

**Ejemplo de Solicitud:**
```bash
GET /api?action=audit&action=approve_spot&limit=20&offset=0
Authorization: Bearer <admin_token>
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 42,
        "moderator_id": "mod-uuid-123",
        "action": "approve_spot",
        "target_type": "spot",
        "target_id": "456",
        "old_value": {"status": "pending"},
        "new_value": {"status": "approved"},
        "reason": "Content meets quality standards",
        "metadata": {
          "ip": "192.168.1.100",
          "user_agent": "Mozilla/5.0..."
        },
        "created_at": "2025-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 142,
      "limit": 20,
      "offset": 0,
      "has_more": true
    }
  }
}
```

---

### 2. Estadísticas de Auditoría (Admin-only)

**Endpoint:** `GET /api?action=audit&sub=stats`

**Query Parameters:**
- `date_from` - Fecha inicio (YYYY-MM-DD)
- `date_to` - Fecha fin (YYYY-MM-DD)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "actions": {
      "approve_spot": 342,
      "reject_spot": 87,
      "delete_comment": 23,
      "ban_user": 5
    },
    "top_moderators": [
      {
        "moderator_id": "mod-123",
        "username": "alice",
        "full_name": "Alice Moderator",
        "role": "moderator",
        "total_actions": 256,
        "first_action": "2024-12-01T09:00:00Z",
        "last_action": "2025-01-15T14:22:00Z"
      }
    ]
  }
}
```

---

### 3. Historial de Moderador Específico (Admin-only)

**Endpoint:** `GET /api?action=audit&sub=moderator&id={moderatorId}`

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "moderator_id": "mod-uuid-123",
    "logs": [...],
    "total": 256
  }
}
```

---

### 4. Historial de Recurso Específico (Moderator+)

**Endpoint:** `GET /api?action=audit&sub=resource&target_type=spot&target_id=456`

**Autenticación:** Bearer token (role: moderator o superior)

**Uso:** Ver todo el historial de acciones sobre un spot/comentario/usuario específico

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "target_type": "spot",
    "target_id": "456",
    "audit_trail": [
      {
        "id": 42,
        "moderator_id": "mod-123",
        "action": "approve_spot",
        "created_at": "2025-01-15T10:30:00Z"
      },
      {
        "id": 51,
        "moderator_id": "mod-456",
        "action": "feature_spot",
        "created_at": "2025-01-16T08:15:00Z"
      }
    ],
    "total": 2
  }
}
```

---

## 📝 Uso Programático

### Registrar Acción de Moderación

```php
<?php
require_once __DIR__ . '/src/AuditLogger.php';
require_once __DIR__ . '/src/Database.php';

$db = \SpotMap\Database::getConnection();
$auditLogger = new AuditLogger($db);

// Ejemplo: Aprobar un spot
$logId = $auditLogger->logModeration(
    moderatorId: 'mod-uuid-123',
    action: 'approve_spot',
    targetType: 'spot',
    targetId: '456',
    oldValue: ['status' => 'pending'],
    newValue: ['status' => 'approved'],
    reason: 'Content meets quality standards',
    metadata: [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'referrer' => $_SERVER['HTTP_REFERER'] ?? null
    ]
);

if ($logId) {
    echo "Audit log created with ID: $logId";
} else {
    echo "Failed to create audit log";
}
```

### Consultar Logs con Filtros

```php
$filters = [
    'action' => 'approve_spot',
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
];

$logs = $auditLogger->getLogs($filters, limit: 50, offset: 0);
$total = $auditLogger->getCount($filters);

echo "Found $total matching logs\n";
foreach ($logs as $log) {
    echo "- {$log['action']} on {$log['target_type']} #{$log['target_id']}\n";
}
```

### Obtener Estadísticas

```php
// Estadísticas por tipo de acción
$stats = $auditLogger->getStatsByAction('2025-01-01', '2025-01-31');
foreach ($stats as $action => $count) {
    echo "$action: $count times\n";
}

// Top moderadores más activos
$topMods = $auditLogger->getModeratorActivity(limit: 10, dateFrom: '2025-01-01');
foreach ($topMods as $mod) {
    echo "{$mod['username']}: {$mod['total_actions']} actions\n";
}
```

---

## 🚀 Implementación en Endpoints de Moderación

Los siguientes endpoints **automáticamente registran** acciones en el audit log:

### Aprobar Spot
```http
POST /api?action=admin&sub=spots&id=456&approve=1
Authorization: Bearer <moderator_token>
Content-Type: application/json

{
  "reason": "High quality content, verified location"
}
```

### Rechazar Spot
```http
POST /api?action=admin&sub=spots&id=789&reject=1
Authorization: Bearer <moderator_token>
Content-Type: application/json

{
  "reason": "Inappropriate content, violates community guidelines"
}
```

**Ambos endpoints:**
1. Verifican rol de moderador/admin
2. Actualizan el estado del spot
3. **Registran la acción en `moderation_audit_log`**
4. Invalidan caché
5. Devuelven confirmación

---

## 🔒 Seguridad y Buenas Prácticas

### Principios de Diseño

1. **Inmutabilidad**: Los registros NO se pueden modificar ni eliminar
2. **Granularidad**: Cada acción individual genera un log separado
3. **Contexto completo**: Se captura IP, user-agent, timestamp, razón
4. **Acceso restringido**: Solo admins pueden ver logs completos
5. **Rendimiento**: Índices en campos comunes de búsqueda

### Consideraciones de Privacidad (GDPR)

- Los logs contienen direcciones IP → deben retenerse según política de privacidad
- Recomendación: Retención de 90 días para logs operacionales, 1 año para investigaciones
- Implementar rotación automática de logs antiguos:

```sql
-- Script de limpieza (ejecutar mensualmente)
DELETE FROM moderation_audit_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Consultas de Alto Rendimiento

Los índices están optimizados para:
- Búsqueda por moderador (`idx_moderator_id`)
- Búsqueda por acción (`idx_action`)
- Búsqueda por recurso (`idx_target`)
- Búsqueda por fecha (`idx_created_at`)
- Estadísticas agregadas (`idx_action_created`)

---

## 📊 Casos de Uso

### 1. Investigación de Quejas
```sql
-- Ver todas las acciones sobre un spot reportado
SELECT * FROM moderation_audit_log
WHERE target_type = 'spot' AND target_id = '456'
ORDER BY created_at DESC;
```

### 2. Revisión de Desempeño de Moderador
```sql
-- Conteo de acciones por moderador en el mes actual
SELECT 
    u.username,
    COUNT(*) as total_actions,
    GROUP_CONCAT(DISTINCT action) as action_types
FROM moderation_audit_log mal
JOIN users u ON mal.moderator_id = u.id
WHERE mal.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY mal.moderator_id
ORDER BY total_actions DESC;
```

### 3. Detección de Patrones Sospechosos
```sql
-- Moderadores con alta tasa de rechazos
SELECT 
    moderator_id,
    COUNT(*) as total_rejects
FROM moderation_audit_log
WHERE action = 'reject_spot'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY moderator_id
HAVING total_rejects > 50;
```

---

## 🧪 Testing

### Ejecutar Tests Unitarios

```bash
cd backend
vendor/bin/phpunit tests/AuditLoggerTest.php
```

**Cobertura de tests:**
- ✅ Logging con datos completos y mínimos
- ✅ Validación de campos requeridos
- ✅ Filtrado por moderador, acción, target
- ✅ Paginación correcta
- ✅ Conteo de registros
- ✅ Estadísticas agregadas
- ✅ Actividad de moderadores (leaderboard)
- ✅ Preservación de caracteres Unicode

---

## 🆕 Migraciones

### Instalación en Base de Datos Existente

```bash
# Opción 1: MySQL CLI
mysql -u root -p spotmap < backend/init-db/migration_add_audit_log.sql

# Opción 2: phpMyAdmin
# Importar archivo: backend/init-db/migration_add_audit_log.sql

# Opción 3: Script PHP
php backend/init-database.php
```

**Verificación post-migración:**
```sql
SHOW TABLES LIKE 'moderation_audit_log';
DESCRIBE moderation_audit_log;
SELECT COUNT(*) FROM moderation_audit_log; -- Debe ser 0 inicialmente
```

---

## 📚 Archivos del Sistema

| Archivo | Propósito |
|---------|-----------|
| `backend/init-db/schema.sql` | Schema completo con tabla de auditoría |
| `backend/init-db/migration_add_audit_log.sql` | Migración independiente |
| `backend/src/AuditLogger.php` | Clase de servicio principal |
| `backend/src/Controllers/AuditController.php` | Controlador de API |
| `backend/src/Controllers/SpotController.php` | Endpoints approve/reject con logging |
| `backend/tests/AuditLoggerTest.php` | Suite de tests unitarios |
| `backend/docs/AUDIT_SYSTEM.md` | Esta documentación |

---

## 🔮 Roadmap Futuro

### Mejoras Planificadas

- [ ] Dashboard visual con gráficos de actividad
- [ ] Alertas automáticas para patrones anómalos
- [ ] Exportación de logs a CSV/JSON para análisis externo
- [ ] Integración con sistema de notificaciones (email a admins)
- [ ] Retención configurable por tipo de acción
- [ ] Firma digital de logs para prevenir manipulación
- [ ] Logs de auditoría para cambios en roles de usuarios

---

## 🆘 Soporte

Para preguntas o problemas:
1. Revisar logs en `backend/logs/app.log`
2. Verificar permisos de base de datos
3. Consultar tests unitarios como ejemplos de uso
4. Abrir issue en el repositorio del proyecto

---

**Versión:** 1.0.0  
**Fecha de Creación:** Enero 2025  
**Autor:** Antonio Valero  
**Proyecto:** SpotMap - Sistema de Auditoría de Moderación
