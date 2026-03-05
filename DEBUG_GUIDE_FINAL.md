# 🔍 Guía de Debug - Sistema de Filtrado SpotMap

## ✅ FIXED: Los módulos ahora están disponibles

La consola debería mostrar:
```
[MAIN] 🐛 Debug tools disponibles: window.spotsModule, window.applyFilters, etc
```

Ahora puedes usar directamente en la consola:

```javascript
window.spotsModule         // ✅ Funciones de spots
window.mapModule           // ✅ Funciones del mapa
window.uiModule            // ✅ Funciones de UI
window.filterState         // ✅ Estado de filtros
window.applyFilters        // ✅ Función para aplicar filtros
```

---

## 🚨 PROBLEMA: Tags = 0

El log muestra: `[UI] Tags extraídos: 0 etiquetas []`

**Por qué:** El spot cargado NO tiene etiquetas en la BD

**Solución:**
1. Crea un **nuevo spot** con tags (`etiqueta1, etiqueta2`)
2. O edita uno existente y agrega tags
3. Recarga la página (F5)

---

## 🧪 Comandos Para Usar YA en Consola

### Test 1: Ver spots y tags
```javascript
const spots = await window.spotsModule.loadSpots()
console.log('Total spots:', spots.length)
console.log('Tags encontrados:', window.spotsModule.getTags(spots))
```

### Test 2: Ver estado de filtros
```javascript
console.log('Estado filtros:', window.filterState)
```

### Test 3: Forzar aplicación de filtros
```javascript
await window.applyFilters()
// Verá logs [UI] mostrando cada paso
```

### Test 4: Ver estructura de un spot
```javascript
const spots = await window.spotsModule.loadSpots()
console.table(spots[0])
```

### Test 5: Iniciar busca
```javascript
window.filterState.search = 'tu búsqueda'
await window.applyFilters()
```

### Test 6: Probar filtro de tags
```javascript
window.filterState.tag = 'playa'  // Nombre de una etiqueta
await window.applyFilters()
```

### Test 7: Probar filtro de distancia
```javascript
window.filterState.enableDistance = true
window.filterState.userLocation = { lat: 40.4168, lng: -3.7038 }
window.filterState.distanceKm = 20
await window.applyFilters()
```

### Test 8: Limpiar todos los filtros
```javascript
window.filterState = {
    search: '',
    category: 'all',
    tag: 'all',
    onlyMine: false,
    distanceKm: 50,
    enableDistance: false,
    userLocation: null
}
await window.applyFilters()
```

---

## ✅ Pasos Recomendados AHORA

1. **Recarga la página** (F5)
2. **Abre la consola** (F12)
3. **Verifica que ves:**
   ```
   [MAIN] 🐛 Debug tools disponibles: window.spotsModule, window.applyFilters, etc
   ```
4. **Copia/pega esto en consola:**
   ```javascript
   const spots = await window.spotsModule.loadSpots()
   console.log('Spots:', spots.length)
   console.log('Tags:', window.spotsModule.getTags(spots).length)
   ```
5. **Si Tags = 0:** Crea un nuevo spot con etiquetas y recarga

---

## 📊 Checklist de Lo Que Debe Funcionar

- [ ] `window.spotsModule` está definido
- [ ] `window.applyFilters` está definido
- [ ] `window.filterState` está definido
- [ ] Los spots se cargan (ves 1 o más)
- [ ] Tags aparecen en el selector (si spots tienen tags)
- [ ] Click en tags filtra spots
- [ ] Distancia muestra km en tarjetas
- [ ] Contador de caracteres funciona en formulario

---

## 🔧 Si Algo No Funciona

**Copia de la consola lo que dice [ERROR] o [MAIN] ❌ y comparte**

