<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';

const props = defineProps({
  spots: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  selectedSpot: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['select-spot']);

const mapRoot = ref(null);
let map = null;
let layerGroup = null;
let markerById = new Map();
const DEFAULT_CENTER = [40.4168, -3.7038];
const DEFAULT_ZOOM = 6;

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function distanceKm([lat1, lng1], [lat2, lng2]) {
  const r = 6371;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const a = Math.sin(dLat / 2) ** 2
    + Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLng / 2) ** 2;
  return 2 * r * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function getSmartFitCoords(coords) {
  if (coords.length <= 2) {
    return coords;
  }

  const center = coords.reduce(
    (acc, [lat, lng]) => [acc[0] + lat / coords.length, acc[1] + lng / coords.length],
    [0, 0],
  );

  const distances = coords.map((point) => distanceKm(center, point)).sort((a, b) => a - b);
  const p80 = distances[Math.floor(distances.length * 0.8)] || 0;
  const thresholdKm = Math.max(350, p80 * 1.8);
  const filtered = coords.filter((point) => distanceKm(center, point) <= thresholdKm);

  return filtered.length >= 2 ? filtered : coords;
}

function fitMapToCoords(coords) {
  if (!map || coords.length === 0) {
    return;
  }

  if (coords.length === 1) {
    map.setView(coords[0], 12);
    return;
  }

  const smartCoords = getSmartFitCoords(coords);
  const lats = smartCoords.map(([lat]) => lat);
  const lngs = smartCoords.map(([, lng]) => lng);
  const latSpan = Math.max(...lats) - Math.min(...lats);
  const lngSpan = Math.max(...lngs) - Math.min(...lngs);

  if (latSpan > 35 || lngSpan > 50) {
    map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    return;
  }

  map.fitBounds(smartCoords, { padding: [40, 40], maxZoom: 12 });
}

function getCoords(spot) {
  const lat = Number(spot?.latitude ?? spot?.lat);
  const lng = Number(spot?.longitude ?? spot?.lng);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
    return null;
  }
  return [lat, lng];
}

function renderMarkers() {
  if (!map || !layerGroup) {
    return;
  }

  layerGroup.clearLayers();
  markerById = new Map();
  const bounds = [];

  props.spots.forEach((spot) => {
    const coords = getCoords(spot);
    if (!coords) {
      return;
    }

    bounds.push(coords);
    const marker = L.marker(coords).addTo(layerGroup);
    const safeTitle = escapeHtml(spot.title || 'Spot');
    const safeCategory = escapeHtml(spot.category || 'sin categoría');
    const safeImage = escapeHtml(spot.imagePath || '');
    const imageBlock = spot.imagePath
      ? `<div style="margin-top:8px;"><img src="${safeImage}" alt="${safeTitle}" style="width:100%;max-width:220px;max-height:120px;object-fit:cover;border-radius:6px;" /></div>`
      : '';
    marker.bindPopup(`<strong>${safeTitle}</strong><br>${safeCategory}${imageBlock}`);
    marker.on('click', () => {
      emit('select-spot', spot.id);
    });
    markerById.set(String(spot.id), marker);
  });

  if (bounds.length > 0) {
    fitMapToCoords(bounds);
  }
}

onMounted(() => {
  map = L.map(mapRoot.value, {
    zoomControl: true,
  }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map);

  layerGroup = L.layerGroup().addTo(map);
  renderMarkers();
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
    layerGroup = null;
    markerById = new Map();
  }
});

watch(
  () => props.spots,
  () => {
    renderMarkers();
  },
  { deep: true },
);

watch(
  () => props.selectedSpot,
  (spot) => {
    if (!map || !spot) {
      return;
    }
    const coords = getCoords(spot);
    if (!coords) {
      return;
    }
    map.flyTo(coords, 14, { duration: 0.6 });
    const marker = markerById.get(String(spot.id));
    if (marker) {
      marker.openPopup();
    }
  },
  { deep: true },
);
</script>

<template>
  <section class="map-panel">
    <div ref="mapRoot" class="map-root" aria-label="Mapa de spots"></div>
    <div v-if="loading" class="map-overlay">Cargando mapa y marcadores…</div>
    <div v-else-if="error" class="map-overlay map-overlay--error">{{ error }}</div>
    <div v-else-if="spots.length === 0" class="map-overlay">No hay spots para mostrar en el mapa.</div>
  </section>
</template>
