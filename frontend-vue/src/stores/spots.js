import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiFetch } from '../services/api';
import { filterValidCoords, normalizeSpot } from '../services/normalizers';

export const useSpotsStore = defineStore('spots', () => {
  const spots = ref([]);
  const loading = ref(false);
  const error = ref('');
  const page = ref(1);
  const limit = ref(24);
  const total = ref(0);
  const pages = ref(1);

  const searchQuery = ref('');
  const categoryFilter = ref('all');
  const tagFilter = ref('all');
  const viewMode = ref('list');
  const userLocation = ref(null);
  const distanceEnabled = ref(false);
  const maxDistanceKm = ref(50);
  const ownerOnly = ref(false);
  const currentUserId = ref('');

  const hasPrev = computed(() => page.value > 1);
  const hasNext = computed(() => page.value < pages.value);

  const availableCategories = computed(() => {
    const values = new Set(
      spots.value
        .map((spot) => String(spot?.category || '').trim())
        .filter(Boolean),
    );
    return Array.from(values).sort((a, b) => a.localeCompare(b, 'es', { sensitivity: 'base' }));
  });

  const availableTags = computed(() => {
    const values = new Set();
    spots.value.forEach((spot) => {
      const tags = Array.isArray(spot?.tags) ? spot.tags : [];
      tags.forEach((tag) => {
        const clean = String(tag || '').trim();
        if (clean) values.add(clean);
      });
    });
    return Array.from(values).sort((a, b) => a.localeCompare(b, 'es', { sensitivity: 'base' }));
  });

  const filteredSpots = computed(() => {
    const search = searchQuery.value.trim().toLowerCase();

    function distanceKm(spot) {
      if (!distanceEnabled.value || !userLocation.value) return 0;
      const lat1 = (userLocation.value.lat * Math.PI) / 180;
      const lon1 = (userLocation.value.lng * Math.PI) / 180;
      const lat2 = (spot.lat * Math.PI) / 180;
      const lon2 = (spot.lng * Math.PI) / 180;
      const dLat = lat2 - lat1;
      const dLon = lon2 - lon1;
      const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return 6371 * c;
    }

    return spots.value.filter((spot) => {
      const title = String(spot?.title || '').toLowerCase();
      const description = String(spot?.description || '').toLowerCase();
      const category = String(spot?.category || '').toLowerCase();
      const tags = Array.isArray(spot?.tags)
        ? spot.tags.map((tag) => String(tag || '').toLowerCase()).filter(Boolean)
        : [];

      const matchesSearch =
        !search ||
        title.includes(search) ||
        description.includes(search) ||
        category.includes(search) ||
        tags.some((tag) => tag.includes(search));

      const matchesCategory = categoryFilter.value === 'all' || String(spot?.category || '') === categoryFilter.value;
      const matchesTag = tagFilter.value === 'all' || tags.includes(String(tagFilter.value || '').toLowerCase());
      const matchesDistance = !distanceEnabled.value || !userLocation.value || distanceKm(spot) <= maxDistanceKm.value;
      const matchesOwner = !ownerOnly.value || !currentUserId.value || String(spot.userId ?? '') === String(currentUserId.value);

      return matchesSearch && matchesCategory && matchesTag && matchesDistance && matchesOwner;
    });
  });

  function buildQueryParams() {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
    });

    return params.toString();
  }

  async function loadSpots() {
    loading.value = true;
    error.value = '';

    try {
      const payload = await apiFetch(`/spots?${buildQueryParams()}`);
      const data = payload?.data ?? {};
      const apiSpots = Array.isArray(data?.spots) ? data.spots : [];
      const pagination = data?.pagination ?? {};

      spots.value = filterValidCoords(apiSpots.map(normalizeSpot));
      total.value = Number(pagination?.total) || spots.value.length;
      pages.value = Math.max(1, Number(pagination?.pages) || 1);
      page.value = Math.max(1, Number(pagination?.page) || 1);
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'No se pudieron cargar los spots.';
      spots.value = [];
      total.value = 0;
      pages.value = 1;
    } finally {
      loading.value = false;
    }
  }

  async function reload() {
    page.value = 1;
    await loadSpots();
  }

  async function nextPage() {
    if (!hasNext.value || loading.value) return;
    page.value += 1;
    await loadSpots();
  }

  async function prevPage() {
    if (!hasPrev.value || loading.value) return;
    page.value -= 1;
    await loadSpots();
  }

  async function setCategoryFilter(value) {
    categoryFilter.value = value || 'all';
    page.value = 1;
    await loadSpots();
  }

  async function setTagFilter(value) {
    tagFilter.value = value || 'all';
    page.value = 1;
    await loadSpots();
  }

  function setSearchQuery(value) {
    searchQuery.value = String(value ?? '');
  }

  function setViewMode(value) {
    viewMode.value = value === 'grid' ? 'grid' : 'list';
  }

  function setUserLocation(coords) {
    if (!coords || !Number.isFinite(coords.lat) || !Number.isFinite(coords.lng)) {
      userLocation.value = null;
      return;
    }
    userLocation.value = { lat: Number(coords.lat), lng: Number(coords.lng) };
  }

  function setDistanceEnabled(value) {
    distanceEnabled.value = Boolean(value);
  }

  function setMaxDistanceKm(value) {
    const numeric = Number(value);
    maxDistanceKm.value = Number.isFinite(numeric) ? Math.min(200, Math.max(1, numeric)) : 50;
  }

  function setOwnerOnly(value) {
    ownerOnly.value = Boolean(value);
  }

  function setCurrentUserId(value) {
    currentUserId.value = String(value ?? '').trim();
  }

  async function resetFilters() {
    searchQuery.value = '';
    categoryFilter.value = 'all';
    tagFilter.value = 'all';
    distanceEnabled.value = false;
    maxDistanceKm.value = 50;
    ownerOnly.value = false;
    page.value = 1;
    await loadSpots();
  }

  return {
    spots,
    filteredSpots,
    loading,
    error,
    page,
    limit,
    total,
    pages,
    hasPrev,
    hasNext,
    viewMode,
    searchQuery,
    categoryFilter,
    tagFilter,
    userLocation,
    distanceEnabled,
    maxDistanceKm,
    ownerOnly,
    currentUserId,
    availableCategories,
    availableTags,
    loadSpots,
    reload,
    nextPage,
    prevPage,
    setSearchQuery,
    setCategoryFilter,
    setTagFilter,
    setViewMode,
    setUserLocation,
    setDistanceEnabled,
    setMaxDistanceKm,
    setOwnerOnly,
    setCurrentUserId,
    resetFilters,
  };
});
