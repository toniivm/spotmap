# SpotMap Legacy → Vue Parity Matrix

## Objetivo
Conservar toda la información funcional del frontend legacy (`frontend/js`) y migrarla por bloques al frontend nuevo (`frontend-vue/src`) sin perder contratos de negocio.

## Entry points y contratos base
- Legacy entrypoint: `frontend/index.html` + `frontend/js/main.js`
- Vue entrypoint: `frontend-vue/src/main.js` + `frontend-vue/src/App.vue`
- API base compartida: `backend/public/index.php`

## Mapa de módulos legacy

### Núcleo de exploración
- `frontend/js/map.js`
  - Contrato: inicializar mapa, pintar marcadores, foco por spot, popup con metadata.
  - Vue equivalente: `frontend-vue/src/components/MapView.vue`.
  - Estado: **migrado parcial-alto** (mapa, marcadores, popup, selección bidireccional).

- `frontend/js/spots.js`
  - Contrato: carga paginada, búsqueda/filtros, CRUD, distancia, owner filter, paginación utilitaria.
  - Vue equivalente: `frontend-vue/src/stores/spots.js` + `SpotSidebar.vue`.
  - Estado: **migrado parcial** (lectura/filtros/paginación/distancia); CRUD/social pendiente.

- `frontend/js/ui.js`
  - Contrato: estado de filtros, render de lista, estados vacío/loading/error, chips, contador, load-more.
  - Vue equivalente: `frontend-vue/src/components/SpotSidebar.vue`.
  - Estado: **migrado parcial** (estructura y filtros principales); refinamiento visual pendiente.

### Auth y sesión
- `frontend/js/auth.js`
  - Contrato: login/register/logout, sesión, rol, fallback local, integración notificaciones.
  - Vue equivalente: `frontend-vue/src/services/auth.js` + `stores/auth.js`.
  - Estado: **migrado parcial** (login/logout/sesión fallback local); registro/OAuth pendiente.

- `frontend/js/oauth.js`
  - Contrato: inicio/callback/link/unlink OAuth.
  - Vue equivalente: pendiente.
  - Estado: **no migrado**.

### Social y detalle
- `frontend/js/social.js`
  - Contrato: likes/favorites/share y carga de favoritos.
  - Vue equivalente: pendiente.
  - Estado: **no migrado**.

- `frontend/js/comments.js`
  - Contrato: comentarios por spot y modal de detalle.
  - Vue equivalente: pendiente.
  - Estado: **no migrado**.

### Moderación y notificaciones
- `frontend/js/moderation.js`
  - Contrato: listado pending + approve/reject + panel moderador.
  - Vue equivalente: pendiente.
  - Estado: **no migrado**.

- `frontend/js/notificationsManager.js` + `notifications.js`
  - Contrato: polling, unread count, mark-read/all, toast.
  - Vue equivalente: pendiente (toast básico no centralizado).
  - Estado: **no migrado**.

### Utilidades transversales
- `frontend/js/theme.js`
  - Contrato: toggle y persistencia tema.
  - Vue equivalente: pendiente.

- `frontend/js/i18n.js`
  - Contrato: catálogo ES/EN y toggle.
  - Vue equivalente: pendiente.

- `frontend/js/imageValidator.js`
  - Contrato: validación de imágenes para upload.
  - Vue equivalente: pendiente.

- `frontend/js/spotMapPicker.js` + `mapPickerModal.js`
  - Contrato: selección en mapa para formulario de alta + previews.
  - Vue equivalente: pendiente.

## Endpoints legacy usados
- Spots lectura/escritura
  - `/spots`
  - `/spots/{id}`
  - `/spots/{id}/photo`
- Auth local fallback
  - `/auth-login.php`
- Moderación
  - `/api/admin/pending`
  - `/api/admin/spots/{id}/approve`
  - `/api/admin/spots/{id}/reject`
- Notificaciones
  - `/api/notifications*`

## Estado actual de migración (resumen)
- **P0 Fundaciones**: completo
- **P1 Núcleo lectura**: en progreso avanzado
  - ✅ mapa+lista+filtros texto/categoría/tag
  - ✅ paginación
  - ✅ distancia + geolocalización
  - ✅ selección lista↔mapa
  - 🔄 paridad UX final con legacy (contador/chips/microinteracciones)

## Siguiente orden recomendado (sin perder contexto legacy)
1. Cerrar P1 visual/UX al 100%.
2. Migrar P2 auth completo (register + estado rol + base OAuth hooks).
3. Migrar P3 escritura (crear/editar + validación imagen + picker mapa).
4. Migrar P4 moderación/notificaciones.
