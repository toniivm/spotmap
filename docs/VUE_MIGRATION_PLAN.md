# SpotMap → Vue Migration Plan (Incremental)

## Objetivo
Migrar el frontend ES Modules actual a Vue 3 sin parar desarrollo ni romper producción.

## Enfoque recomendado
- **No rewrite completo**: migración por módulos.
- **Dual-run temporal**: `frontend/` (actual) y `frontend-vue/` (nuevo) conviven.
- **Backend/API intacto**: PHP + endpoints se mantienen.

## Estado inicial (hecho)
- ✅ Base Vue creada en `frontend-vue/` con Vite.
- ✅ Config runtime para detectar ruta del proyecto.
- ✅ Cliente API mínimo.
- ✅ Store de spots con Pinia.
- ✅ Sidebar + mapa Leaflet responsivo funcional.

## Fases de migración

### Fase 0 — Fundaciones (1-2 días)
- Estructura base Vue + convenciones.
- Config común de API/auth/runtime.
- Layout shell responsivo estable.
- Criterio de salida:
  - App Vue levanta en local y lista spots en mapa.

### Fase 1 — Núcleo de lectura (3-5 días)
- Sidebar filtros básicos (texto/categoría/tags).
- Lista y grid de spots.
- Sincronización selección lista ↔ mapa.
- Criterio de salida:
  - Paridad funcional de exploración con frontend actual.

Estado actual: ✅ Completada en `frontend-vue` (filtros, lista/grid, mapa sincronizado, geolocalización, distancia, owner filter, paginación y UX de exploración rematada).

### Fase 2 — Autenticación y sesión (3-4 días)
- Login/register UI en Vue.
- Integración Supabase auth.
- Estado global usuario/rol.
- Criterio de salida:
  - Usuario puede iniciar sesión y ver UI contextual.

### Fase 3 — Escritura y social (4-6 días)
- Crear/editar spots.
- Likes/favoritos.
- Comentarios.
- Criterio de salida:
  - Flujos CRUD y social operativos en Vue.

### Fase 4 — Moderación y notificaciones (3-5 días)
- Panel moderación.
- Notificaciones (lista + unread count).
- Criterio de salida:
  - Flujos de moderador 100% operativos.

### Fase 5 — QA, performance y cutover (2-4 días)
- E2E críticos en Vue.
- Ajustes responsive móvil y accesibilidad.
- Estrategia de switch de entrypoint (`index.html` / ruta Vue).
- Criterio de salida:
  - Vue pasa smoke tests y reemplaza frontend legado.

## Estrategia de convivencia
1. Mantener `frontend/index.html` como principal en producción.
2. Publicar Vue en ruta separada temporal (ej. `/frontend-vue/`).
3. Validar módulo por módulo con checklist de paridad.
4. Cambiar entrypoint cuando paridad + QA estén cerrados.

## Riesgos y mitigaciones
- **Riesgo**: desalineación API/contratos.
  - Mitigación: centralizar cliente API y normalizadores en Vue.
- **Riesgo**: regresiones responsive móviles.
  - Mitigación: pruebas emuladas iPhone X/Pixel + Playwright.
- **Riesgo**: desvío de alcance.
  - Mitigación: mover módulos en orden de impacto/valor.

## Definición de “done” final
- Todos los flujos principales activos en Vue.
- Sin errores críticos en consola.
- E2E críticos verdes.
- Performance y UX móvil aceptables.
- Frontend legado listo para retiro controlado.
