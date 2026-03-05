# SpotMap - Estado Real de Tareas

**Última actualización:** 02 Mar 2026  
**Estado general (real):** 5/15 completadas, 9 parciales, 1 pendiente crítica

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
**Estado real:** Existen workflows de CI, seguridad y deploy en `.github/workflows`.

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
