# 🛠️ CLI Tools - SpotMap Monitoring

Herramientas de línea de comandos para gestionar logs y monitorear SpotMap.

---

## 📋 Contenido

1. [cli-logs.php](#cli-logs) - Gestión avanzada de logs
2. [health-check.php](#health-check) - Verificación automática de salud del sistema

---

## <a name="cli-logs"></a>cli-logs.php - Gestión de Logs

Herramienta completa para inspeccionar, filtrar, exportar y gestionar logs del sistema.

### Instalación

```bash
cd /var/www/spotmap/backend
php cli-logs.php help
```

### Comandos Disponibles

#### 1. **tail** - Ver últimos logs
```bash
php cli-logs.php tail [limite]
```

**Ejemplos:**
```bash
php cli-logs.php tail              # Últimos 20 logs
php cli-logs.php tail 50           # Últimos 50 logs
php cli-logs.php tail 100          # Últimos 100 logs
```

**Salida:**
```
📋 Últimos 20 logs:
────────────────────────────────────────────────────────────────────────────────
2025-12-09 14:23:45 [ERROR   ] Database connection failed
  └─ Context: {"errno":"ECONNREFUSED","host":"localhost"}
2025-12-09 14:22:30 [INFO    ] User logged in successfully
2025-12-09 14:21:15 [SECURITY] Suspicious login attempt from 192.168.1.100
```

#### 2. **filter** - Filtrar por nivel
```bash
php cli-logs.php filter <nivel> [limite]
```

**Niveles disponibles:**
- `debug` - Información de debug
- `info` - Información general
- `warning` - Advertencias
- `error` - Errores
- `critical` - Errores críticos
- `security` - Eventos de seguridad

**Ejemplos:**
```bash
php cli-logs.php filter error              # Últimos 50 errores
php cli-logs.php filter error 100          # Últimos 100 errores
php cli-logs.php filter critical           # Últimos 50 críticos
php cli-logs.php filter security 20        # Últimos 20 eventos de seguridad
```

**Salida:**
```
🔍 Filtrando por nivel: ERROR (últimos 50)
────────────────────────────────────────────────────────────────────────────────
2025-12-09 14:23:45 - Database connection failed
  └─ {"errno":"ECONNREFUSED","host":"localhost"}
2025-12-09 14:15:20 - File not found
  └─ {"file":"/var/www/spotmap/public/missing.html"}
```

#### 3. **alerts** - Ver alertas generadas
```bash
php cli-logs.php alerts [limite]
```

**Ejemplos:**
```bash
php cli-logs.php alerts            # Últimas 20 alertas
php cli-logs.php alerts 50         # Últimas 50 alertas
```

**Salida:**
```
🚨 Últimas 20 alertas:
────────────────────────────────────────────────────────────────────────────────
2025-12-09 14:23:45 [CRITICAL] Database connection failed
2025-12-09 14:10:30 [CRITICAL] Memory limit exceeded
```

#### 4. **metrics** - Ver resumen de métricas
```bash
php cli-logs.php metrics
```

**Salida:**
```
📊 Resumen de Métricas:
────────────────────────────────────────────────────────────────────────────────
Total Requests: 1523
Average Response Time: 45.23 ms
Error Rate: 2.5%
Memory Usage:
  • current_mb: 12.5
  • peak_mb: 25.3
  • average_mb: 18.2
```

#### 5. **stats** - Estadísticas generales
```bash
php cli-logs.php stats
```

**Salida:**
```
📈 Estadísticas de Logs:
────────────────────────────────────────────────────────────────────────────────
Conteo por nivel:
  DEBUG      : 234
  INFO       : 1203
  WARNING    : 45
  ERROR      : 12
  CRITICAL   : 2
  SECURITY   : 8

Total de logs: 1504
```

#### 6. **clean** - Limpiar logs antiguos
```bash
php cli-logs.php clean [días]
```

**Ejemplos:**
```bash
php cli-logs.php clean             # Eliminar logs > 7 días
php cli-logs.php clean 30          # Eliminar logs > 30 días
php cli-logs.php clean 1           # Eliminar logs > 1 día
```

**Salida:**
```
🧹 Limpiando logs más antiguos que 7 días...
────────────────────────────────────────────────────────────────────────────────
✓ Eliminado: application.log.1
✓ Eliminado: metrics.json.1
✓ Eliminado: alerts.log.1

✅ Limpieza completada: 3 archivos eliminados
```

#### 7. **export** - Exportar logs
```bash
php cli-logs.php export <json|csv> [limite]
```

**Ejemplos:**
```bash
php cli-logs.php export json           # Exportar 1000 últimos en JSON
php cli-logs.php export json 500       # Exportar 500 últimos en JSON
php cli-logs.php export csv            # Exportar 1000 últimos en CSV
php cli-logs.php export csv 2000       # Exportar 2000 últimos en CSV
```

**Salida:**
```
💾 Exportando 1000 logs a /var/www/spotmap/backend/logs/export_2025-12-09_14-25-30.json

✅ Exportación completada
```

**Archivos generados:**
- `logs/export_2025-12-09_14-25-30.json` - Formato JSON estructurado
- `logs/export_2025-12-09_14-25-30.csv` - Formato CSV (excel)

#### 8. **view** - Ver archivo específico
```bash
php cli-logs.php view [archivo] [líneas]
```

**Ejemplos:**
```bash
php cli-logs.php view                           # Últimas 50 líneas de application.log
php cli-logs.php view application.log 100       # Últimas 100 líneas
php cli-logs.php view metrics.json 20           # Últimas 20 líneas de metrics
php cli-logs.php view alerts.log                # Últimas 50 líneas de alerts
```

#### 9. **files** - Listar archivos disponibles
```bash
php cli-logs.php files
```

**Salida:**
```
📂 Archivos de logs disponibles:
────────────────────────────────────────────────────────────────────────────────
  • application.log (2.34 MB) - Modificado: 2025-12-09 14:25:30
  • metrics.json (512 KB) - Modificado: 2025-12-09 14:25:20
  • alerts.log (45 KB) - Modificado: 2025-12-09 14:15:45
  • health-check-2025-12-09.json (12 KB) - Modificado: 2025-12-09 14:00:00
```

#### 10. **help** - Mostrar ayuda
```bash
php cli-logs.php help
```

---

## <a name="health-check"></a>health-check.php - Verificación de Salud

Herramienta automática para verificar el estado general del sistema y generar reportes.

### Uso

```bash
php health-check.php
```

### Verificaciones Realizadas

**🖥️ Sistema**
- Memoria actual y pico
- Límite de memoria PHP
- Tiempo activo del servidor

**💾 Base de Datos**
- Verificación de conexión
- Tipo (PDO local o Supabase)
- Estado de conectividad

**📁 Almacenamiento**
- Tamaño del directorio de logs
- Tamaño del directorio de uploads
- Espacio disponible en disco
- Uso de almacenamiento

**⚙️ Archivos Críticos**
- Existencia de archivos esenciales
- Integridad de la estructura

**🔐 Permisos**
- Permisos de escritura en directorios
- Acceso a logs y uploads

**📊 Estadísticas de Logs**
- Conteo de errores
- Conteo de eventos críticos

### Salida Ejemplo

```
╔═══════════════════════════════════════════╗
║  SpotMap - Health Check Report            ║
║  2025-12-09 14:30:45                      ║
╚═══════════════════════════════════════════╝

🖥️  Sistema
────────────────────────────────────────────
Memoria actual: 12.45 MB
Memoria pico:   25.67 MB
Límite PHP:     256M
Tiempo activo:  up 45 days, 12:30, 2 users, load average: 0.45, 0.38, 0.42

💾 Base de Datos
────────────────────────────────────────────
Estado:     HEALTHY
Tipo:       PDO local conectada

📁 Almacenamiento
────────────────────────────────────────────
Logs:       HEALTHY (2.34 MB)
Uploads:    HEALTHY (125.67 MB)
Espacio disponible: HEALTHY (45.23%)
Espacio libre:      456.78 GB

⚙️  Archivos Críticos
────────────────────────────────────────────
✓ Config
✓ Logger
✓ API
✓ Database

🔐 Permisos
────────────────────────────────────────────
✓ logs es escribible
✓ uploads es escribible
✓ config es escribible

📊 Estadísticas de Logs
────────────────────────────────────────────
Errores:    12
Críticos:   2
Estado:     HEALTHY

📈 Estado General
────────────────────────────────────────────
Estado:     HEALTHY
Críticos:   0
Advertencias: 0

✅ Reporte guardado: health-check-2025-12-09.json
```

### Configurar en Cron

Para ejecutar automáticamente cada hora:

```bash
# Editar cron
crontab -e

# Agregar línea
0 * * * * php /var/www/spotmap/backend/health-check.php > /dev/null 2>&1
```

Para ejecutar cada 30 minutos:

```bash
*/30 * * * * php /var/www/spotmap/backend/health-check.php
```

Para ejecutar cada día a las 2 AM:

```bash
0 2 * * * php /var/www/spotmap/backend/health-check.php >> /var/www/spotmap/backend/logs/cron-health.log 2>&1
```

### Reportes Automáticos

Los reportes se guardan en:
- `logs/health-check-YYYY-MM-DD.json` - Reporte en JSON

Ejemplo de reporte JSON:
```json
{
  "timestamp": "2025-12-09 14:30:45",
  "overall_status": "healthy",
  "critical_count": 0,
  "warning_count": 0,
  "checks": {
    "system": {
      "status": "healthy",
      "memory_usage_mb": 12.45,
      "memory_peak_mb": 25.67,
      "memory_limit": "256M"
    },
    "database": {
      "status": "healthy",
      "type": "PDO local conectada"
    },
    "storage": {
      "status": "healthy",
      "logs_mb": 2.34,
      "uploads_mb": 125.67,
      "disk_usage_percent": 54.77
    },
    "logs": {
      "status": "healthy",
      "errors": 12,
      "criticals": 2
    }
  }
}
```

---

## 📚 Casos de Uso Comunes

### 1. Revisar logs de hoy
```bash
php cli-logs.php tail 100
```

### 2. Encontrar todos los errores de las últimas 24h
```bash
php cli-logs.php filter error 500
```

### 3. Generar reporte mensual de alertas
```bash
php cli-logs.php export json 10000 > monthly_report.json
```

### 4. Limpiar logs de más de 30 días
```bash
php cli-logs.php clean 30
```

### 5. Verificar salud del sistema
```bash
php health-check.php
```

### 6. Monitorear en tiempo real (requiere watch)
```bash
watch -n 5 'php cli-logs.php stats'
```

---

## 🔍 Búsqueda Avanzada

Para búsquedas más complejas, exportar a JSON y procesar:

```bash
# Exportar logs
php cli-logs.php export json 1000

# Procesar con jq (si está disponible)
cat logs/export_*.json | jq '.[] | select(.level=="ERROR")'

# O con grep
grep '"level":"ERROR"' logs/export_*.json | wc -l
```

---

## 📊 Análisis de Tendencias

### Contar errores por hora
```bash
grep '"level":"ERROR"' logs/application.log | cut -d' ' -f1-2 | uniq -c
```

### Ver usuarios con más errores
```bash
grep '"level":"ERROR"' logs/application.log | grep -o '"user_id":[0-9]*' | sort | uniq -c
```

### Endpoints más lentos
```bash
grep '"response_time_ms"' logs/metrics.json | sort -t':' -k2 -rn | head -20
```

---

## ⚠️ Troubleshooting

### "No hay logs disponibles"
```bash
# Verificar que el directorio existe
ls -la logs/

# Crear si no existe
mkdir -p logs
chmod 755 logs
```

### "Acceso denegado a archivos"
```bash
# Ajustar permisos
chmod 644 logs/application.log
chmod 755 logs/
```

### "Memoria agotada durante exportación"
```bash
# Exportar en porciones menores
php cli-logs.php export json 500
```

---

**⚠️ CONFIDENCIAL - NO COMPARTIR**
Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
