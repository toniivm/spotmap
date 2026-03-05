# 🛡️ Sistema de Aprobación y Moderación - SpotMap

## Descripción General

El sistema de **3 niveles de aprobación** para SpotMap permite que:
1. **Usuarios normales** crean spots que necesitan aprobación (estado: `pending`)
2. **Admins/Moderadores** pueden crear spots que se publican directamente (estado: `approved`)
3. **Moderadores** pueden revisar, aprobar o rechazar spots pendientes

---

## 🎯 Flujo de Trabajo

### 1. Crear un Spot

#### 👤 Usuario Normal
```
[Clicks "+ Añadir Spot"]
    ↓
[Completa formulario]
    ↓
[Sube foto(s)]
    ↓
[Status: PENDING] ⏳
    ↓
[Aparece con badge "⏳ Pendiente"]
    ↓
[Visible solo para el usuario y moderadores]
```

**Mensaje mostrado:**
```
✅ Spot creado correctamente

Tu spot está siendo verificado por nuestro equipo 
y aparecerá públicamente en breve.
```

#### 🛡️ Admin/Moderador
```
[Clicks "+ Añadir Spot"]
    ↓
[Completa formulario]
    ↓
[Sube foto(s)]
    ↓
[Status: APPROVED] ✅ (automático)
    ↓
[Aparece inmediatamente en buscador]
    ↓
[Visible para todos]
```

**Mensaje mostrado:**
```
✓ Spot publicado correctamente
```

---

## 🛡️ Panel de Moderación

### Acceso
- **Solo visible para:** Admins y Moderadores
- **Ubicación:** Botón "🛡️ Moderación" en navbar (al lado de "+ Añadir Spot")
- **Badge:** Muestra número de spots pendientes `[3]`

### Interfaz

```
╔═══════════════════════════════════════════════╗
║     🛡️ Panel de Moderación                    ║
║                                               ║
║ 3 spot(s) pendiente(s) de aprobación         ║
├───────────────────────────────────────────────┤
│ [Imagen]    │ Título del Spot               │
│             │ Descripción...                 │
│             │ 📍 Ubicación                   │
│             │ 🏷️ Categoría                  │
│             │ ⏰ Fecha                       │
│             │                               │
│             │ [✅ Aprobar] [❌ Rechazar]    │
├───────────────────────────────────────────────┤
│ [Siguiente spot...]                           │
└───────────────────────────────────────────────┘
```

### Acciones

#### ✅ Aprobar
- **Cambio de estado:** `pending` → `approved`
- **Visibilidad:** Pasa a ser visible públicamente
- **Notificación:** Mensaje de éxito

#### ❌ Rechazar
- **Cambio de estado:** `pending` → `rejected`
- **Razón:** Solicitada al moderador (opcional)
- **Visibilidad:** El spot se oculta
- **Usuario:** No recibe notificación (puede mejorarse agregando email)

---

## 🔀 Comportamiento de Filtros

### Búsqueda Principal (lista pública)
- **Usuarios normales:** ✅ Solo spots `approved`
- **Admins/Moderadores:** ✅ Spots `approved` + `pending` (con badge visual)

### Mis Spots
- **Usuarios normales:** ✅ Todos sus spots (pending y approved)
- **Admins/Moderadores:** ✅ Todos sus spots (pending y approved)

### Mapa
- **Público:** ✅ Solo spots `approved`
- **Autenticado:** ✅`approved` + propios `pending`

---

## 🗄️ Base de Datos

### Tabla: `spots`

```sql
Column          Type              Current Values
─────────────────────────────────────────────────
status          TEXT              'approved', 'pending', 'rejected'
created_at      TIMESTAMP         Fecha de creación
updated_at      TIMESTAMP         Última actualización
rejection_reason TEXT (opcional)   Motivo del rechazo
```

**Estados:**
- `approved` - Visible públicamente
- `pending` - Necesita aprobación
- `rejected` - Rechazado por moderador

---

## 📝 Validaciones

### Al Crear Spot (Usuario Normal)
1. ✅ Usuario autenticado
2. ✅ Los roles "moderator" y "admin" → status `approved`
3. ✅ Los roles otros → status `pending`
4. ✅ Al menos una imagen requerida
5. ✅ Título y ubicación requeridos

### Al Crear Spot (Admin/Moderador)
- Mismo que arriba, pero status es automáticamente `approved`

### Al Moderar
- ✅ Solo moderadores pueden ver el panel
- ✅ Solo pueden aprobar/rechazar sus propios spots o cualquier spot pendiente
- ✅ Se invalida el caché de spots después de aprobar/rechazar

---

## 🎨 Indicadores Visuales

### Status Pending
```
┌─────────────────────────────┐
│ 🏖️ Playa de las Catedrales  │
│ ...                          │
│ ┌────────────────────────┐  │
│ │⏳ En verificación      │  │
│ │Este spot está siendo   │  │
│ │revisado...             │  │
│ └────────────────────────┘  │
│ [Badge: ⏳ Pendiente]       │
└─────────────────────────────┘
```

### Status Approved
```
┌─────────────────────────────┐
│ 🏖️ Playa de las Catedrales  │
│ ...                          │
│ [No hay bandera de alerta]   │
│ [Badge: playa]              │
└─────────────────────────────┘
```

---

## 📊 Roles y Permisos

| Acción | Usuario | Moderador | Admin |
|--------|---------|-----------|-------|
| Crear spot con pending | ✅ | ❌ | ❌ |
| Crear spot con approved | ❌ | ✅ | ✅ |
| Ver panel de moderación | ❌ | ✅ | ✅ |
| Aprobar/Rechazar spots | ❌ | ✅ | ✅ |
| Ver spots pendientes | Propios | Todos | Todos |
| Editar spots propios | ✅ | ✅ | ✅ |
| Eliminar spots propios | ✅ | ✅ | ✅ |

---

## 💻 Código

### Archivo: `frontend/js/moderation.js`
- `isModerator()` - Verificar si usuario es moderador
- `loadPendingSpots()` - Cargar spots con estado `pending`
- `approveSpot(spotId)` - Aprobar un spot
- `rejectSpot(spotId, reason)` - Rechazar un spot
- `setupModerationPanel()` - Configurar UI del panel
- `initModeration()` - Inicializar sistema

### Archivo: `frontend/js/supabaseSpots.js`
```javascript
// En createSpotRecord():
const user = getCurrentUser();
const status = (user?.role === 'admin' || user?.role === 'moderator') 
  ? 'approved' 
  : 'pending';
```

### Archivo: `frontend/js/main.js`
```javascript
import { initModeration } from './moderation.js';
// ...
initModeration(); // Fase 5
```

---

## 🚀 Mejoras Futuras

1. **Email directo:** Notificar al usuario cuando su spot es aprobado/rechazado
2. **Razón detallada:** Guardar razón del rechazo en `rejection_reason`
3. **Reapproach:** Permitir que usuarios modifiquen spots rechazados
4. **Analytics:** Dashboard con estadísticas de aprobaciones
5. **Límite de tiempo:** Auto-rechazo si no se aprueba en X días
6. **Comentarios de moderación:** Permitir feedback entre moderadores

---

## 📞 Soporte

**Problema:** Spot no aparece en búsqueda principal
- **Verificar:** `status = 'approved'` en base de datos

**Problema:** No veo el botón de moderación
- **Verificar:** Rol del usuario es `moderator` o `admin`
- **Verificar:** Usuario está autenticado
- **Verificar:** hay spots con `status = 'pending'`

**Problema:** Error al aprobar spot
- **Ver consola:** F12 → Console → buscar `[MODERATION]`
- **Verificar:** Permisos RLS en Supabase

---

## 📋 Checklist de Implementación

- ✅ Modificar `supabaseSpots.js` para determinar status según rol
- ✅ Crear `moderation.js` con toda la lógica de moderación
- ✅ Importar `moderation.js` en `main.js`
- ✅ Agregar botón en `index.html`
- ✅ Mostrar badge con contador de pendientes
- ✅ Panel de moderación funcional
- ✅ Validaciones de permisos
- ⏳ Pruebas completas
- ⏳ Documentación de usuario

---

## 🔒 Consideraciones de Seguridad

1. **RLS en Supabase:** Las políticas de seguridad validan que:
   - Los usuarios solo vean spots `approved`
   - Los moderadores vean todos los estados
   - Los moderadores solo puedan cambiar status

2. **Permisos en Frontend:** Se validan pero no son suficientes
   - Siempre validar en backend/Supabase también

3. **Caché:** Se invalida después de aprobar/rechazar
   - Evita mostrar información stale

---

**Versión:** 1.0.0  
**Última actualización:** 15/02/2026  
**Estado:** ✅ Implementado
