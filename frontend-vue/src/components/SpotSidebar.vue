<script setup>
import { nextTick, ref, watch } from 'vue';

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
  selectedId: {
    type: [String, Number, null],
    default: null,
  },
  viewMode: {
    type: String,
    default: 'list',
  },
  searchQuery: {
    type: String,
    default: '',
  },
  categoryFilter: {
    type: String,
    default: 'all',
  },
  tagFilter: {
    type: String,
    default: 'all',
  },
  availableCategories: {
    type: Array,
    default: () => [],
  },
  availableTags: {
    type: Array,
    default: () => [],
  },
  page: {
    type: Number,
    default: 1,
  },
  pages: {
    type: Number,
    default: 1,
  },
  total: {
    type: Number,
    default: 0,
  },
  hasPrev: {
    type: Boolean,
    default: false,
  },
  hasNext: {
    type: Boolean,
    default: false,
  },
  distanceEnabled: {
    type: Boolean,
    default: false,
  },
  maxDistanceKm: {
    type: Number,
    default: 50,
  },
  hasUserLocation: {
    type: Boolean,
    default: false,
  },
  ownerOnly: {
    type: Boolean,
    default: false,
  },
  canFilterOwner: {
    type: Boolean,
    default: false,
  },
  canManageSpots: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'select-spot',
  'reload',
  'change-view',
  'change-search',
  'change-category',
  'change-tag',
  'toggle-distance',
  'change-distance',
  'use-my-location',
  'toggle-owner-only',
  'edit-spot',
  'delete-spot',
  'clear-filters',
  'prev-page',
  'next-page',
]);

const spotButtonRefs = ref(new Map());

function handleClick(spotId) {
  emit('select-spot', spotId);
}

function setSpotRef(spotId, element) {
  if (!element) {
    spotButtonRefs.value.delete(String(spotId));
    return;
  }
  spotButtonRefs.value.set(String(spotId), element);
}

watch(
  () => props.selectedId,
  async (id) => {
    if (id === null || id === undefined) return;
    await nextTick();
    const target = spotButtonRefs.value.get(String(id));
    if (target && typeof target.scrollIntoView === 'function') {
      target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  },
);

function formatTags(tags) {
  if (!Array.isArray(tags) || tags.length === 0) {
    return [];
  }
  return tags.slice(0, 3);
}

function trimDescription(value) {
  const text = String(value || '').trim();
  if (!text) return 'Sin descripción';
  return text.length > 88 ? `${text.slice(0, 88)}…` : text;
}

function hasActiveFilters() {
  return Boolean(
    String(props.searchQuery || '').trim()
    || props.categoryFilter !== 'all'
    || props.tagFilter !== 'all'
    || props.distanceEnabled
    || props.ownerOnly,
  );
}

function handleEdit(spot, event) {
  event?.stopPropagation?.();
  emit('edit-spot', spot);
}

function handleDelete(spot, event) {
  event?.stopPropagation?.();
  emit('delete-spot', spot);
}
</script>

<template>
  <aside class="sidebar">
    <div class="sidebar-head">
      <div>
        <h2>Explorar Spots</h2>
        <p class="sidebar-subtitle">Filtra y navega spots de forma rápida</p>
      </div>
      <button class="btn-reload" type="button" @click="emit('reload')">Recargar</button>
    </div>

    <div class="sidebar-summary">
      <span>Mostrando {{ spots.length }} de {{ total }}</span>
      <span>·</span>
      <span>Página {{ page }} de {{ pages }}</span>
    </div>

    <div class="filters">
      <div class="filter-group">
        <label class="filter-label" for="search-input">Búsqueda</label>
        <input
          id="search-input"
          class="input"
          type="search"
          :value="searchQuery"
          placeholder="Buscar por título, descripción o tag"
          @input="emit('change-search', $event.target.value)"
        >
      </div>

      <div class="filter-group">
        <label class="filter-label">Filtros rápidos</label>
        <div class="row">
          <select class="input" :value="categoryFilter" @change="emit('change-category', $event.target.value)">
            <option value="all">Todas las categorías</option>
            <option v-for="category in availableCategories" :key="category" :value="category">{{ category }}</option>
          </select>

          <select class="input" :value="tagFilter" @change="emit('change-tag', $event.target.value)">
            <option value="all">Todas las etiquetas</option>
            <option v-for="tag in availableTags" :key="tag" :value="tag">{{ tag }}</option>
          </select>
        </div>
      </div>

      <button class="btn-reload" type="button" @click="emit('use-my-location')">📍 Mi ubicación</button>

      <label class="distance-toggle">
        <input type="checkbox" :checked="distanceEnabled" @change="emit('toggle-distance', $event.target.checked)">
        Filtrar por distancia
      </label>

      <div v-if="distanceEnabled" class="distance-box">
        <label for="distance-range">Máximo: {{ maxDistanceKm }} km</label>
        <input
          id="distance-range"
          type="range"
          min="1"
          max="200"
          :value="maxDistanceKm"
          @input="emit('change-distance', Number($event.target.value))"
        >
        <small v-if="!hasUserLocation">Activa tu ubicación para aplicar este filtro</small>
      </div>

      <label v-if="canFilterOwner" class="distance-toggle">
        <input type="checkbox" :checked="ownerOnly" @change="emit('toggle-owner-only', $event.target.checked)">
        Solo mis spots
      </label>

      <div class="view-toggle" role="group" aria-label="Modo de visualización">
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'list' }"
          type="button"
          @click="emit('change-view', 'list')"
        >Lista</button>
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'grid' }"
          type="button"
          @click="emit('change-view', 'grid')"
        >Tarjetas</button>
      </div>

      <div class="quick-actions">
        <button class="btn-reload" type="button" @click="emit('clear-filters')">Limpiar filtros</button>
      </div>
    </div>

    <ul v-if="loading" class="spot-list spot-list--skeleton" aria-hidden="true">
      <li v-for="n in 6" :key="`skeleton-${n}`" class="spot-skeleton">
        <div class="spot-skeleton__thumb"></div>
        <div class="spot-skeleton__line spot-skeleton__line--title"></div>
        <div class="spot-skeleton__line"></div>
        <div class="spot-skeleton__line spot-skeleton__line--short"></div>
      </li>
    </ul>
    <div v-else-if="error" class="state-block error">{{ error }}</div>
    <div v-else-if="spots.length === 0" class="state-block">
      No hay spots para los filtros actuales.
      <button v-if="hasActiveFilters()" class="btn-reload state-action" type="button" @click="emit('clear-filters')">Mostrar todos</button>
    </div>

    <ul v-else class="spot-list" :class="{ grid: viewMode === 'grid' }">
      <li v-for="spot in spots" :key="spot.id">
        <button
          :ref="(el) => setSpotRef(spot.id, el)"
          class="spot-item"
          :class="{ active: String(selectedId) === String(spot.id) }"
          type="button"
          @click="handleClick(spot.id)"
        >
          <img v-if="spot.imagePath" :src="spot.imagePath" :alt="spot.title || 'Imagen del spot'" loading="lazy">
          <div class="spot-item__head">
            <strong>{{ spot.title || 'Sin título' }}</strong>
            <span class="spot-item__category">{{ spot.category || 'sin categoría' }}</span>
          </div>
          <p class="spot-item__description">{{ trimDescription(spot.description) }}</p>
          <div v-if="formatTags(spot.tags).length > 0" class="spot-item__tags">
            <span v-for="tag in formatTags(spot.tags)" :key="`${spot.id}-${tag}`" class="spot-item__tag">#{{ tag }}</span>
          </div>
          <div v-if="canManageSpots" class="spot-item__actions">
            <button class="spot-item__action" type="button" @click="handleEdit(spot, $event)">Editar</button>
            <button class="spot-item__action spot-item__action--danger" type="button" @click="handleDelete(spot, $event)">Eliminar</button>
          </div>
          <small v-else>sin etiquetas</small>
        </button>
      </li>
    </ul>

    <div class="pagination">
      <span class="page-info">Página {{ page }} / {{ pages }} · Total {{ total }}</span>
      <div class="page-actions">
        <button class="btn-page" type="button" :disabled="!hasPrev || loading" @click="emit('prev-page')">Anterior</button>
        <button class="btn-page" type="button" :disabled="!hasNext || loading" @click="emit('next-page')">Siguiente</button>
      </div>
    </div>
  </aside>
</template>
