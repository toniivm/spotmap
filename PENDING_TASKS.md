# SpotMap - Estado Real de Tareas

**Última actualización:** 06 Mar 2026  
**Estado operativo verificado:** seguridad/auth reforzada, tests backend/frontend ejecutables, quedan gaps de release en CI, paridad Vue y mobile QA

---

## Plan operativo P1/P2 (06 Mar 2026)

### P1 - Bloqueantes de release (ejecutar primero)

1. Pipeline CI real en repositorio
- Estado: completado (06 Mar 2026).
- Evidencia: workflow `/.github/workflows/ci.yml` con jobs de frontend, backend y E2E smoke.
- Entregable: lint+test+build frontend, phpunit backend (descarga PHPUnit 10 en CI) y Playwright smoke/recovery desktop+mobile.

2. E2E de negocio completo (desktop + mobile)
- Estado: parcial.
- Hecho: smoke + auth + recovery en Playwright.
- Falta: flujo end-to-end con moderacion/notificaciones y ejecucion estable en CI.
- Entregable: spec unica de negocio (login -> crear pending -> aprobar/rechazar -> notificacion visible).

3. Hardening accesibilidad de modales
- Estado: parcial.
- Evidencia: no hay patron `inert`/focus-trap unificado para todos los modales.
- Entregable: helper comun de modal accessibility + tests E2E de teclado/escape/focus return.

4. Cierre seguridad operativa de configuracion
- Estado: parcial.
- Hecho: fallback JWT endurecido + debug bool correcto + admin hardcode removido.
- Falta: documentar checklist deploy y asegurar que `ALLOW_INSECURE_JWT_FALLBACK=false` en todos los entornos.

### P2 - Paridad funcional y performance

1. Paridad Vue vs legacy en social/features pendientes
- Estado: pendiente parcial-alto.
- Evidencia: faltan modulos Vue equivalentes de favoritos/comentarios/theme/i18n.
- Entregable: migracion por modulos con matriz de paridad cerrada.

2. Cache de roles con TTL
- Estado: pendiente.
- Evidencia: `backend/src/Roles.php` cachea en memoria sin TTL/invalidacion.
- Entregable: TTL configurable + invalidacion segura + tests.

3. Conversion de imagen a WebP en upload
- Estado: parcial.
- Evidencia: se valida/acepta webp, pero no hay conversion automatica JPEG/PNG -> WebP.
- Entregable: pipeline opcional de conversion (GD/Imagick) con fallback seguro.

4. Mobile readiness formal
- Estado: parcial.
- Hecho: layout responsive funcional y proyecto Playwright mobile.
- Falta: matriz dispositivos, performance 4G, ajustes tactiles finos y criterio de "mobile listo" certificado.

---

## ✅ Leyenda de estado

- `[x]` Completada
- `[~]` Parcial (funciona, pero faltan remates)
- `[ ]` Pendiente

---

## 🔴 Prioridad inmediata recomendada

### [~] 6. Setup tests E2E frontend (con foco móvil) ← EN MARCHA
**Razón:** Es lo que más reduce riesgo antes de producción y valida versión móvil real.  
**Hecho en esta sesión:**
- Playwright instalado y configurado
- Smoke E2E creado (home + login modal) en móvil y desktop
- Ejecución mobile conectada en CI
**Siguiente remate:**
- Flujo autenticado completo: login → crear spot → moderar → notificación

---

## 📌 Estado por tarea (15 originales)

### [~] 1. Remover debug logs innecesarios
**Estado real:** Se limpió parte del debug antiguo, pero aún hay logs de desarrollo en frontend/backend.

### [x] 2. Implementar notificaciones spot aprobado/rechazado
**Estado real:** Implementado end-to-end (rutas, controlador, UI y polling).

### [x] 3. Refactorizar validación backend (robustez)
**Estado real:** Implementado (`Constants`, rol por perfil, sanitización y validaciones reforzadas).

### [x] 4. Implementar paginación spots
**Estado real:** Ya implementado en backend y frontend con estado de paginación y caché.

### [~] 5. Setup tests unitarios backend
**Estado real:** Hay base de tests robusta, pero faltan casos críticos concretos de moderación/RLS.

### [~] 6. Setup tests E2E frontend
**Estado real:** Playwright base ya implementado (`frontend/e2e`, `playwright.config.js`, scripts npm y CI mobile smoke). Falta flujo de negocio autenticado completo.

### [~] 7. Documentación API (Swagger/OpenAPI)
**Estado real:** `backend/openapi.json` está amplio, pero requiere validación final contra todas las rutas activas y ejemplos de error.

### [~] 8. Revisar y mejorar rate limiting
**Estado real:** Existe `RateLimiter` y test básico, pero falta ajuste fino por endpoint/riesgo.

### [~] 9. Verificar security headers CORS
**Estado real:** Headers y CORS existen, pero falta endurecer política de orígenes en producción y tests más estrictos.

### [x] 10. Implementar CI/CD pipeline
**Estado real:** Workflow CI versionado en `.github/workflows/ci.yml` con validaciones frontend, backend y E2E smoke.

### [~] 11. Sistema de auditoría para moderación
**Estado real:** Logging de auditoría y migración implementados; falta dashboard/visualización operativa.

### [ ] 12. Mejorar caché usuario/roles
**Estado real:** Sigue pendiente cacheo/TTL explícito para evitar consultas repetidas.

### [x] 13. Error handling centralizado
**Estado real:** Implementado (`frontend/js/errorHandler.js`) e integrado en módulos principales.

### [~] 14. Optimizar carga de imágenes (WebP)
**Estado real:** Se acepta WebP, pero no está cerrada la conversión automática JPEG → WebP en upload.

### [~] 15. Fix aria-hidden en modales
**Estado real:** Mitigado en parte, pero no estandarizado con `inert` en todos los modales.

---

## 📱 Versión móvil (nueva línea de trabajo obligatoria)

### Objetivo
Garantizar experiencia mobile-first usable y verificable en dispositivos táctiles.

### Backlog móvil (prioridad alta)
- [ ] Definir breakpoints objetivo y matriz de dispositivos (iPhone 13, Pixel 7, iPad)
- [ ] Revisar navegación, modales y formularios para uso táctil (tap targets, scroll, teclado)
- [ ] Ajustar paneles (sidebar/moderación/notificaciones) para pantallas <768px
- [ ] Validar rendimiento móvil (LCP/CLS) en red 4G simulada
- [ ] Añadir tests E2E mobile en CI (Playwright projects: `mobile`, `desktop`)

### Criterio de “móvil listo”
- Sin overflow horizontal
- Todos los flujos críticos completables con una mano
- E2E passing en viewport móvil y desktop

---

## 📊 Resumen de progreso (real)

| Estado | Cantidad |
|--------|----------|
| Completadas | 5 |
| Parciales | 9 |
| Pendientes | 1 |
| **Total** | **15** |

---

## 🎯 Próximas 2 sesiones recomendadas

### Sesión A (ahora)
1. Crear 1 flujo E2E autenticado completo (login → spot → moderación → notificación)
2. Validar ese flujo en `mobile-chromium` y `desktop-chromium`

### Sesión B
1. Cerrar caché de rol (`Auth` + frontend)
2. Rematar accesibilidad de modales (`inert`) y WebP conversion

---

**Next review:** al cerrar E2E mobile smoke
