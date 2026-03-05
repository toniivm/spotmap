# 🔍 Guía de Debug - Sistema de Filtrado SpotMap

## 🚨 PROBLEMA PRINCIPAL: Tags = 0

**El log muestra:** `[UI] Tags extraídos: 0 etiquetas []`

**Significa:** Los spots cargados NO tienen etiquetas en la BD.

### ✅ Solución:

1. **Opción A: Crear un nuevo spot CON tags**
   - Click "+ Añadir Spot"
   - Rellena campos básicos (título, ubicación)
   - En "Etiquetas", escribe tags separadas por coma: `playa, naturaleza, foto`
   - Guarda el spot
   - Recarga la página (F5)
   - Ahora deberían aparecer tags

2. **Opción B: Editar un spot existente**
   - Abre un spot
   - Click en editar (botón lápiz)
   - Agrega campos de tags
   - Guarda
   - Recarga página

3. **Opción C: Verificar BD directamente**
   ```sql
   -- Si usas MySQL/Supabase:
   SELECT id, title, tags FROM spots LIMIT 1;
   -- Debe tener datos en la columna "tags"
   ```

---

## ✅ Ahora todos los módulos están en window

Después de las correcciones, puedes acceder en la consola a:

```javascript
window.spotsModule         // Funciones de spots
window.mapModule           // Funciones del mapa  
window.filterState         // Estado de filtros
window.applyFilters()      // Función para aplicar filtros
window.debugInfo           // Info de debug
```

---

## 🧪 Tests Rápidos en Consola

### Test 1: Verificar módulos cargados
```javascript
console.log({
  spotsModule: window.spotsModule ? '✅' : '❌',
  debugInfo: window.debugInfo ? '✅' : '❌',
  applyFilters: typeof window.applyFilters === 'function' ? '✅' : '❌'
})
```

### Test 2: Ver estructura de spots
```javascript
const spots = await window.spotsModule.loadSpots()
console.table(spots.map(s => ({
  id: s.id,
  title: s.title,
  tags: s.tags,
  category: s.category
})))
// Esto mostrará una tabla con todos los spots
```

### Test 3: Debug detallado de tags (verá logs [SPOTS-DEBUG])
```javascript
const spots = await window.spotsModule.loadSpots()
const tags = window.spotsModule.getTags(spots)
console.log('Tags encontrados:', tags)
// En la consola saldrán logs [SPOTS-DEBUG] mostrando cada spot
```

### Test 4: Ver estado de filtros
```javascript
console.log('Estado actual:', window.filterState)
// Debería mostrar algo como:
// { search: '', category: 'all', tag: 'all', onlyMine: false, ... }
```

### Test 5: Forzar aplicación de filtros
```javascript
await window.applyFilters()
// Verá logs [UI] con cada paso del filtrado
```

### Test 6: Verificar todos los elementos HTML existen
```javascript
const elementos = {
  'Filtro de tags': document.getElementById('filter-tag'),
  'Chips de tags': document.getElementById('tag-chips'),
  'Mi spots toggle': document.getElementById('toggle-my-spots'),
  'Distancia toggle': document.getElementById('filter-distance-toggle'),
  'Distancia container': document.getElementById('distance-filter-container'),
  'Distancia slider': document.getElementById('filter-distance'),
  'Contador título': document.getElementById('spot-title-count'),
  'Contador descripción': document.getElementById('spot-description-count')
}

Object.entries(elementos).forEach(([nombre, elemento]) => {
  console.log(`${nombre}:`, elemento ? '✅ ENCONTRADO' : '❌ NO ENCONTRADO')
})
```

---

## 📊 Problemas y Soluciones

| Problema | Causa | Solución |
|----------|-------|-----------|
| Tags = 0 | Spots sin etiquetas | Crea/edita spot con tags |
| spotsModule undefined | Módulos no expuestos | Recarga página (F5) |
| Filtros no funcionan | Elementos HTML no existen | Revisa index.html |
| Distancia no funciona | Geolocalización denegada | Permite ubicación en navegador |
| Contadores no aparecen | Modal aún no abierto | Abre "+ Añadir Spot" |

---

## 🔧 Comandos Útiles

```javascript
// Recargar spots y logs
await window.spotsModule.loadSpots()

// Ver cantidad total de spots
(await window.spotsModule.loadSpots()).length

// Limpiar consola
clear()

// Ver usuario autenticado (si existe)
window.getCurrentUser ? window.getCurrentUser() : 'No disponible'

// Forzar debug completo
console.group('FULL DEBUG')
const spots = await window.spotsModule.loadSpots()
console.log('Total spots:', spots.length)
console.log('Tags:', window.spotsModule.getTags(spots))
console.log('Estados:', window.filterState)
console.groupEnd()
```

---

## ⚡ Pasos Siguientes RECOMENDADOS

1. **Abre la consola** (F12)
2. **Copia/Pega este comando:**
   ```javascript
   const spots = await window.spotsModule.loadSpots()
   console.log('Spots:', spots.length, '| Tags:', window.spotsModule.getTags(spots).length)
   ```
3. **Si tags = 0:**
   - Crea un NEW spot con algo como "etiqueta1, etiqueta2"
   - Recarga página
   - Intenta de nuevo

4. **Si ya tienes tags:**
   - Prueba hacer click en chips de tags
   - Prueba el filtro de distancia
   - Abre "+ Añadir Spot" y verá contadores

---

## 📞 Si Encuentras Errores

Copia los logs que digan `[UI]`, `[SPOTS]`, o `[ERROR]` de la consola y comparte el error.

