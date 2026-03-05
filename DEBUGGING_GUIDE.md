# 🔍 Guía de Debug - Sistema de Filtrado SpotMap
---

## 🚨 PROBLEMA FRECUENTE: Tags = 0

Esto ocurre cuando **los spots no tienen tags en la BD**.

**Por qué pasa:**
- El spot fue creado antes de implementar tags
- O se creó sin rellenar el campo de etiquetas
- O la API no devuelve el campo `tags`

**Solución rápida:**
1. Crea un **nuevo spot** con tags (ej: "playa, naturaleza, foto")
2. O edita uno existente y agrega tags
3. Recarga la página (F5)

Los tags deberían aparecer en el selector.

---

## 🔍 Guía de Debug - Sistema de Filtrado SpotMap
## Cómo Detectar Problemas

### 1. **Abrir Consola del Navegador**
- Windows/Linux: `Ctrl+Shift+J`
- Mac: `Cmd+Option+J`
- O: Click derecho → Inspeccionar → Pestaña "Console"

### 2. **Problemas Comunes y Cómo Verificarlos**

#### ❌ Los tags no aparecen
**Síntoma:** El select "Etiquetas" está vacío o solo dice "Todas las etiquetas"

**Cómo debug:**
```javascript
// En la consola, escribe:
spotsModule.getTags(await spotsModule.loadSpots()).length
// Si devuelve 0 → Los spots no tienen el campo "tags"
// Si devuelve > 0 → Los tags se cargaron correctamente
```

#### ❌ La búsqueda no funciona
**Síntoma:** Los spots no cambian cuando escribo en el buscador

**Cómo verificar en consola:**
```javascript
// Verificar que filterState.search se actualiza:
console.log(window.filterState)
// Debería mostrar: { search: "tu búsqueda", category: "all", ...}

// Verificar que applyFilters() se llama:
// Abre DevTools → Pestaña Network → Filtra por "api"
// Cuando escribas en búsqueda, deberías ver requests a la API
```

#### ❌ La distancia no funciona
**Síntoma:** El toggle de "Filtrar por distancia" no activa el filtrador

**Cómo verificar:**
```javascript
// Verificar si el contenedor existe:
document.getElementById('distance-filter-container')
// Si devuelve null → El HTML no tiene el elemento

// Verificar si la geolocalización está disponible:
navigator.geolocation ? 'Disponible' : 'NO DISPONIBLE'

// Verificar la ubicación del usuario:
console.log(window.filterState.userLocation)
// Debería mostrar: { lat: 40.123, lng: -3.456 }
```

#### ❌ Los contadores de caracteres no funcionan
**Síntoma:** El contador "0/255" no aparece o no se actualiza

**Cómo verificar:**
```javascript
// Verificar que los elementos existen:
document.getElementById('spot-title-count')
document.getElementById('spot-description-count')
// Si devuelven null → Revisa el HTML

// Cuando abras el modal (click en "+ Añadir Spot"):
// El contador debería actualizar al escribir
```

---

## 📋 Checklist de Inicialización

Cuando la página carga, abre la Consola y verifica que ves estos mensajes:

```
[UI] Elementos de distancia: Todos encontrados ✅
[UI] Tags extraídos: X etiquetas ✅
[UI] Configurando validación de título ✅
[UI] Configurando validación de descripción ✅
[MAIN] ✅ Aplicación inicializada correctamente ✅
```

Si ves **"NO ENCONTRADO"** o **"Elementos no encontrados"**, significa que el HTML no tiene esos elementos.

---

## 🧪 Tests Manuales

## 🧪 Tests Manuales

### Test 1: Inspeccionar spot actual
```javascript
// Ver la estructura completa del primer spot
const spots = await spotsModule.loadSpots()
console.table(spots[0])

// Ver si tiene el campo "tags"
console.log('¿Tiene tags?', spots[0].tags !== undefined)
```

### Test 2: Debug de Tags
```javascript
// Cargar spots y ver tags
const spots = await spotsModule.loadSpots()
console.log('Total spots:', spots.length)

// Ver tags extraídos (con debug detallado)
const tags = spotsModule.getTags(spots)
console.log('Tags encontrados:', tags)
// En la consola verás [SPOTS-DEBUG] logs mostrando qué tiene cada spot
```

### Test 3: Debug de applyFilters
```javascript
// Ver estado actual
console.log('Estado de filtros:', window.filterState)

// Forzar aplicación de filtros
await window.applyFilters()

// Verás logs [UI] mostrando cada paso
```

### Test 4: Debug de Elementos HTML
```javascript
// Verificar que todos los filtros existen
const elements = {
  tagFilter: document.getElementById('filter-tag'),
  tagChips: document.getElementById('tag-chips'),
  mySpots: document.getElementById('toggle-my-spots'),
  distanceToggle: document.getElementById('filter-distance-toggle'),
  distanceContainer: document.getElementById('distance-filter-container'),
  distanceSlider: document.getElementById('filter-distance'),
  titleCounter: document.getElementById('spot-title-count'),
  descCounter: document.getElementById('spot-description-count')
}

Object.entries(elements).forEach(([name, el]) => {
  console.log(`${name}:`, el ? '✅ OK' : '❌ NO ENCONTRADO')
})
```

### Test 5: Debug completo de un spot
```javascript
// Obtener primer spot
const spots = await spotsModule.loadSpots()
const spot = spots[0]

console.group('📍 Información del Spot')
console.log('ID:', spot.id)
console.log('Título:', spot.title)
console.log('Tags:', spot.tags)
console.log('Categoría:', spot.category)
console.log('Latitud:', spot.lat)
console.log('Longitud:', spot.lng)
console.log('Usuario ID:', spot.user_id)
console.log('Rating:', spot.rating)
console.log('Estado:', spot.status)
console.groupEnd()
```

---

## 🔧 Comandos Útiles en Consola

```javascript
// Mostrar state actual de filtros
window.filterState

// Forzar aplicar filtros
await window.applyFilters()

// Recargar todos los spots sin filtrar
const spots = await spotsModule.loadSpots()
console.log(spots.length + ' spots cargados')

// Ver tags disponibles
spotsModule.getTags(await spotsModule.loadSpots())

// Verificar usuario autenticado
window.getCurrentUser()

// Limpiar filterState
window.filterState = { search: '', category: 'all', tag: 'all', onlyMine: false, distanceKm: 50, enableDistance: false, userLocation: null }
```

---

## ❌ Problemas Frecuentes

| Problema | Causa Probable | Solución |
|----------|---|---|
| Nada funciona | Los módulos no cargaron | Recarga la página (F5) |
| Tags vacÍos | Spots sin campo "tags" | Verifica BD/API devuelve tags |
| Distancia no calcula | Geolocalización denegada | Permite ubicación en permisos |
| Contadores no aparecen | Modal aún no abierto | Abre "+ Añadir Spot" primero |
| My Spots no funciona | No estás autenticado | Inicia sesión primero |

---

## 📞 Reporte de Bugs

Si encuentras un problema:
1. Abre la **Consola (DevTools)**
2. Copia todos los `[UI]`, `[MAIN]` o errores rojos
3. Intenta reproducirlo
4. Incluye esos logs en el reporte

**Ejemplo:**
```
[UI] Filtro de distancia: true
[UI] Contenedor de distancia: visible
[UI] Solicitando ubicación del usuario...
[UI] ✓ Ubicación obtenida: 40.4168, -3.7038
[UI] Resultado final: 12 spots mostrados
```

