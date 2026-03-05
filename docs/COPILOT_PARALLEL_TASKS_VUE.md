# Copilot Parallel Tasks — Vue Migration

Este documento te permite delegar tareas en paralelo (Copilot CLI/Cloud) sin bloquear el avance principal.

## Cómo repartir trabajo
- **Tú + Copilot principal**: arquitectura, integración y decisiones técnicas.
- **Copilot CLI/Cloud (2º plano)**: tareas acotadas, repetitivas y verificables.

## Task Pack A — UI Components (bajo riesgo)
### A1. Navbar responsive Vue
**Prompt sugerido**
"Integrar navegación superior directamente en `App.vue` con botones de sesión y estado de usuario, manteniendo diseño simple y responsive móvil."

### A2. SpotCard + SpotList
**Prompt sugerido**
"Consolidar la lista de spots en `SpotSidebar.vue` con vista lista/grid y evento `select-spot`. No añadir librerías nuevas."

### A3. Empty/Error/Loading states
**Prompt sugerido**
"Estandarizar estados de carga/error/vacío en componentes activos (`SpotSidebar.vue` y `MapView.vue`) sin generar componentes no usados."

## Task Pack B — Estado y API (riesgo medio)
### B1. Normalización de payload Spot
**Prompt sugerido**
"Añade `frontend-vue/src/services/normalizers.js` para normalizar spot (`id,title,description,category,lat,lng,tags,imagePath,status`). Úsalo en el store de spots."

### B2. Filtros en store
**Prompt sugerido**
"Extiende `stores/spots.js` con filtros reactivos por texto/categoría/tags y computed `filteredSpots` sin romper `loadSpots`."

### B3. Cliente auth base
**Prompt sugerido**
"Crear `stores/auth.js` y `services/auth.js` para sesión básica (load session, sign in/out placeholders) compatible con futura integración Supabase."

## Task Pack C — QA y tooling (bajo/medio)
### C1. Tests unitarios de store
**Prompt sugerido**
"Configura Vitest para `frontend-vue` y añade tests para `stores/spots.js` (carga exitosa, error, normalización)."

### C2. Smoke E2E Vue
**Prompt sugerido**
"Añade test Playwright smoke para `frontend-vue`: carga app, renderiza sidebar, renderiza mapa, y comprueba al menos un marcador."

### C3. Lint/format
**Prompt sugerido**
"Configura ESLint + reglas Vue esenciales en `frontend-vue` y aplica fix sobre los archivos nuevos sin tocar frontend legado."

## Reglas para delegar bien
- Pide PRs pequeños (1 task pack por PR).
- Exige criterio de aceptación concreto por tarea.
- Evita cambios en `frontend/` salvo que se pida explícitamente.
- Pide siempre comandos de validación al finalizar.

## Checklist de aceptación por PR delegado
- [ ] Build de `frontend-vue` OK.
- [ ] Sin errores de consola en navegación básica.
- [ ] No rompe endpoints backend existentes.
- [ ] Cambios documentados en 5-10 líneas.
