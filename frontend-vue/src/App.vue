<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';
import SpotSidebar from './components/SpotSidebar.vue';
import MapView from './components/MapView.vue';
import NotificationsDropdown from './components/NotificationsDropdown.vue';
import ModerationPanel from './components/ModerationPanel.vue';
import { useSpotsStore } from './stores/spots';
import { useAuthStore } from './stores/auth';
import { useNotificationsStore } from './stores/notifications';
import { useModerationStore } from './stores/moderation';
import { apiFetch } from './services/api';
import { getStoredAccessToken } from './services/auth';

const spotsStore = useSpotsStore();
const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();
const moderationStore = useModerationStore();
const selectedSpotId = ref(null);
const showLoginModal = ref(false);
const authMode = ref('login');
const registerName = ref('');
const loginEmail = ref('');
const loginPassword = ref('');
const authError = ref('');
const authFieldErrors = ref({
  name: '',
  email: '',
  password: '',
});
const showCreateSpotModal = ref(false);
const createSpotError = ref('');
const createSpotFieldErrors = ref({
  title: '',
  lat: '',
  lng: '',
  images: '',
});
const creatingSpot = ref(false);
const locatingSpotCoords = ref(false);
const showCreateSpotMapPicker = ref(false);
const createSpotMapRoot = ref(null);
const uiToast = ref({
  visible: false,
  type: 'info',
  message: '',
});
const newSpot = ref({
  title: '',
  description: '',
  category: '',
  tags: '',
  lat: '',
  lng: '',
  image1: null,
  image2: null,
});
let toastTimer = null;
let createSpotMap = null;
let createSpotMapMarker = null;

const selectedSpot = computed(() => spotsStore.filteredSpots.find((spot) => String(spot.id) === String(selectedSpotId.value)) ?? null);
const isAuthenticated = computed(() => authStore.isAuthenticated);
const isModerator = computed(() => authStore.isModerator);
const currentUsername = computed(() => authStore.username || 'Usuario');
const currentRoleLabel = computed(() => authStore.roleLabel || 'Usuario');
const oauthProviders = computed(() => authStore.oauthProviders || []);
const hasUserLocation = computed(() => !!spotsStore.userLocation);

const oauthProviderLabels = {
  google: 'Google',
  facebook: 'Facebook',
  twitter: 'Twitter/X',
  instagram: 'Instagram',
};

const oauthProviderIcons = {
  google: '🟢',
  facebook: '🔵',
  twitter: '⚫',
  instagram: '🟣',
};

watch(
  () => authStore.user?.id,
  async (userId, prevUserId) => {
    spotsStore.setCurrentUserId(userId || '');
    if (!userId) {
      spotsStore.setOwnerOnly(false);
      notificationsStore.clearForLogout();
      moderationStore.reset();
      return;
    }

    if (userId !== prevUserId) {
      await notificationsStore.initForSession();
      if (authStore.isModerator) {
        await moderationStore.loadPending();
      }
    }
  },
  { immediate: true },
);

function handleSelectSpot(spotId) {
  selectedSpotId.value = spotId;
}

function clearAuthValidationErrors() {
  authFieldErrors.value = {
    name: '',
    email: '',
    password: '',
  };
}

function clearCreateSpotValidationErrors() {
  createSpotFieldErrors.value = {
    title: '',
    lat: '',
    lng: '',
    images: '',
  };
}

function showToast(message, type = 'info') {
  if (toastTimer) {
    clearTimeout(toastTimer);
  }
  uiToast.value = {
    visible: true,
    type,
    message,
  };
  toastTimer = setTimeout(() => {
    uiToast.value.visible = false;
  }, 3800);
}

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
}

function validateAuthForm() {
  clearAuthValidationErrors();

  const email = String(loginEmail.value || '').trim();
  const password = String(loginPassword.value || '');
  const name = String(registerName.value || '').trim();

  if (!email) {
    authFieldErrors.value.email = 'El email es obligatorio';
  } else if (!isValidEmail(email)) {
    authFieldErrors.value.email = 'El email no tiene formato válido';
  }

  if (!password) {
    authFieldErrors.value.password = 'La contraseña es obligatoria';
  } else if (password.length < 6) {
    authFieldErrors.value.password = 'Mínimo 6 caracteres';
  }

  if (authMode.value === 'register') {
    if (!name) {
      authFieldErrors.value.name = 'El nombre es obligatorio';
    } else if (name.length < 2) {
      authFieldErrors.value.name = 'Mínimo 2 caracteres';
    }
  }

  return !authFieldErrors.value.name && !authFieldErrors.value.email && !authFieldErrors.value.password;
}

function validateCreateSpotForm() {
  clearCreateSpotValidationErrors();

  const title = String(newSpot.value.title || '').trim();
  const lat = Number(newSpot.value.lat);
  const lng = Number(newSpot.value.lng);

  if (!title) {
    createSpotFieldErrors.value.title = 'El título es obligatorio';
  } else if (title.length < 3) {
    createSpotFieldErrors.value.title = 'Mínimo 3 caracteres';
  }

  if (!Number.isFinite(lat)) {
    createSpotFieldErrors.value.lat = 'Latitud inválida';
  } else if (lat < -90 || lat > 90) {
    createSpotFieldErrors.value.lat = 'Debe estar entre -90 y 90';
  }

  if (!Number.isFinite(lng)) {
    createSpotFieldErrors.value.lng = 'Longitud inválida';
  } else if (lng < -180 || lng > 180) {
    createSpotFieldErrors.value.lng = 'Debe estar entre -180 y 180';
  }

  if (!newSpot.value.image1 && !newSpot.value.image2) {
    createSpotFieldErrors.value.images = 'Debes subir al menos una imagen';
  }

  return !createSpotFieldErrors.value.title
    && !createSpotFieldErrors.value.lat
    && !createSpotFieldErrors.value.lng
    && !createSpotFieldErrors.value.images;
}

function openLoginModal() {
  authMode.value = 'login';
  authError.value = '';
  clearAuthValidationErrors();
  showLoginModal.value = true;
}

function closeLoginModal() {
  showLoginModal.value = false;
  authError.value = '';
  loginPassword.value = '';
  clearAuthValidationErrors();
}

function setAuthMode(mode) {
  authMode.value = mode;
  authError.value = '';
  loginPassword.value = '';
  clearAuthValidationErrors();
}

async function handleAuthSubmit() {
  authError.value = '';
  if (!validateAuthForm()) {
    authError.value = 'Revisa los campos obligatorios';
    return;
  }

  try {
    if (authMode.value === 'register') {
      await authStore.register(registerName.value.trim(), loginEmail.value, loginPassword.value);
    } else {
      await authStore.login(loginEmail.value, loginPassword.value);
    }
    closeLoginModal();
    loginPassword.value = '';
    showToast(authMode.value === 'register' ? 'Cuenta creada correctamente' : 'Sesión iniciada', 'success');
  } catch (err) {
    authError.value = err instanceof Error
      ? err.message
      : authMode.value === 'register'
        ? 'No se pudo crear la cuenta'
        : 'No se pudo iniciar sesión';
    showToast(authError.value, 'error');
  }
}

async function handleOAuthLogin(provider) {
  authError.value = '';
  try {
    await authStore.loginWithOAuth(provider);
    showToast(`Redirigiendo a ${oauthProviderLabels[provider] || provider}...`, 'info');
  } catch (err) {
    authError.value = err instanceof Error ? err.message : 'No se pudo iniciar OAuth';
    showToast(authError.value, 'error');
  }
}

function openCreateSpotModal() {
  createSpotError.value = '';
  clearCreateSpotValidationErrors();
  showCreateSpotModal.value = true;
}

function closeCreateSpotModal() {
  showCreateSpotModal.value = false;
  showCreateSpotMapPicker.value = false;
  destroyCreateSpotMap();
  createSpotError.value = '';
  clearCreateSpotValidationErrors();
  newSpot.value = {
    title: '',
    description: '',
    category: '',
    tags: '',
    lat: '',
    lng: '',
    image1: null,
    image2: null,
  };
}

function setSpotImage(index, event) {
  const file = event?.target?.files?.[0] || null;
  if (index === 1) newSpot.value.image1 = file;
  if (index === 2) newSpot.value.image2 = file;
}

function destroyCreateSpotMap() {
  if (createSpotMap) {
    createSpotMap.remove();
    createSpotMap = null;
    createSpotMapMarker = null;
  }
}

function getValidCreateSpotCoords() {
  const lat = Number(newSpot.value.lat);
  const lng = Number(newSpot.value.lng);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
    return null;
  }
  if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
    return null;
  }
  return [lat, lng];
}

function setCreateSpotCoords(lat, lng) {
  const cleanLat = Number(lat);
  const cleanLng = Number(lng);
  if (!Number.isFinite(cleanLat) || !Number.isFinite(cleanLng)) {
    return;
  }
  newSpot.value.lat = cleanLat.toFixed(6);
  newSpot.value.lng = cleanLng.toFixed(6);
  createSpotFieldErrors.value.lat = '';
  createSpotFieldErrors.value.lng = '';
}

function placeCreateSpotMarker(lat, lng, shouldPan = true) {
  if (!createSpotMap) {
    return;
  }

  if (createSpotMapMarker) {
    createSpotMap.removeLayer(createSpotMapMarker);
  }

  createSpotMapMarker = L.marker([lat, lng]).addTo(createSpotMap);
  if (shouldPan) {
    createSpotMap.setView([lat, lng], Math.max(createSpotMap.getZoom(), 13));
  }
}

async function ensureCreateSpotMap() {
  await nextTick();
  if (!createSpotMapRoot.value) {
    return;
  }

  const baseCoords = getValidCreateSpotCoords()
    || (spotsStore.userLocation ? [spotsStore.userLocation.lat, spotsStore.userLocation.lng] : null)
    || [40.4168, -3.7038];

  if (!createSpotMap) {
    createSpotMap = L.map(createSpotMapRoot.value, {
      zoomControl: true,
    }).setView(baseCoords, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(createSpotMap);

    createSpotMap.on('click', (event) => {
      const { lat, lng } = event.latlng;
      setCreateSpotCoords(lat, lng);
      placeCreateSpotMarker(lat, lng, false);
      showToast('Ubicación seleccionada en el mapa', 'success');
    });
  } else {
    createSpotMap.invalidateSize();
  }

  const formCoords = getValidCreateSpotCoords();
  if (formCoords) {
    placeCreateSpotMarker(formCoords[0], formCoords[1], true);
  }
}

async function toggleCreateSpotMapPicker() {
  showCreateSpotMapPicker.value = !showCreateSpotMapPicker.value;
  if (!showCreateSpotMapPicker.value) {
    return;
  }
  await ensureCreateSpotMap();
}

function useCurrentCoordinatesForForm() {
  if (!navigator.geolocation) {
    createSpotError.value = 'Tu navegador no soporta geolocalización';
    showToast(createSpotError.value, 'warning');
    return;
  }

  locatingSpotCoords.value = true;
  navigator.geolocation.getCurrentPosition(
    async ({ coords }) => {
      setCreateSpotCoords(coords.latitude, coords.longitude);
      createSpotError.value = '';
      if (showCreateSpotMapPicker.value) {
        await ensureCreateSpotMap();
      }
      showToast('Ubicación actual cargada', 'success');
      locatingSpotCoords.value = false;
    },
    (error) => {
      const byCode = {
        1: 'Permiso de ubicación denegado',
        2: 'No se pudo obtener tu ubicación',
        3: 'Tiempo de espera agotado al obtener ubicación',
      };
      createSpotError.value = byCode[error?.code] || 'Error al obtener ubicación';
      showToast(createSpotError.value, 'error');
      locatingSpotCoords.value = false;
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 },
  );
}

async function handleCreateSpot() {
  createSpotError.value = '';
  clearCreateSpotValidationErrors();

  const token = getStoredAccessToken();
  if (!token) {
    createSpotError.value = 'Debes iniciar sesión para crear spots';
    showToast(createSpotError.value, 'warning');
    return;
  }

  if (!validateCreateSpotForm()) {
    createSpotError.value = 'Revisa los campos del formulario';
    return;
  }

  creatingSpot.value = true;
  try {
    const form = new FormData();
    form.append('title', String(newSpot.value.title || '').trim());
    form.append('description', String(newSpot.value.description || '').trim());
    form.append('category', String(newSpot.value.category || '').trim());
    form.append('lat', String(newSpot.value.lat || '').trim());
    form.append('lng', String(newSpot.value.lng || '').trim());

    const tags = String(newSpot.value.tags || '')
      .split(',')
      .map((tag) => tag.trim())
      .filter(Boolean);
    form.append('tags', JSON.stringify(tags));

    if (newSpot.value.image1) form.append('image1', newSpot.value.image1);
    if (newSpot.value.image2) form.append('image2', newSpot.value.image2);

    await apiFetch('/spots', { method: 'POST', body: form, token });
    await spotsStore.reload();
    closeCreateSpotModal();
    showToast('Spot creado correctamente', 'success');
  } catch (err) {
    createSpotError.value = err instanceof Error ? err.message : 'No se pudo crear el spot';
    showToast(createSpotError.value, 'error');
  } finally {
    creatingSpot.value = false;
  }
}

async function handleLogout() {
  await authStore.logout();
  showToast('Sesión cerrada', 'info');
}

function handleUseMyLocation() {
  if (!navigator.geolocation) {
    return;
  }
  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      spotsStore.setUserLocation({ lat: coords.latitude, lng: coords.longitude });
    },
    () => {
      spotsStore.setDistanceEnabled(false);
    },
    { enableHighAccuracy: true, timeout: 10000 },
  );
}

onMounted(() => {
  authStore.init();
  spotsStore.loadSpots();
});

onUnmounted(() => {
  destroyCreateSpotMap();
  notificationsStore.stopPolling();
  if (toastTimer) {
    clearTimeout(toastTimer);
  }
});
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="brand-wrap">
        <div class="brand">📸 SpotMap Vue</div>
        <span class="brand-subtitle">Experiencia de exploración y publicación de spots</span>
      </div>
      <div class="topbar-actions">
        <ModerationPanel v-if="isAuthenticated && isModerator" />
        <button v-if="isAuthenticated" class="topbar-btn topbar-btn--primary" type="button" @click="openCreateSpotModal">+ Añadir Spot</button>
        <NotificationsDropdown v-if="isAuthenticated" />
        <span v-if="isAuthenticated" class="meta">Hola, {{ currentUsername }}</span>
        <span v-if="isAuthenticated" class="role-pill">{{ currentRoleLabel }}</span>
        <button v-if="isAuthenticated" class="topbar-btn" type="button" @click="handleLogout">Cerrar sesión</button>
        <button v-else class="topbar-btn" type="button" @click="openLoginModal">Iniciar sesión</button>
      </div>
    </header>

    <main class="layout">
      <SpotSidebar
        :spots="spotsStore.filteredSpots"
        :loading="spotsStore.loading"
        :error="spotsStore.error"
        :selected-id="selectedSpotId"
        :view-mode="spotsStore.viewMode"
        :search-query="spotsStore.searchQuery"
        :category-filter="spotsStore.categoryFilter"
        :tag-filter="spotsStore.tagFilter"
        :available-categories="spotsStore.availableCategories"
        :available-tags="spotsStore.availableTags"
        :distance-enabled="spotsStore.distanceEnabled"
        :max-distance-km="spotsStore.maxDistanceKm"
        :has-user-location="hasUserLocation"
        :owner-only="spotsStore.ownerOnly"
        :can-filter-owner="isAuthenticated"
        :page="spotsStore.page"
        :pages="spotsStore.pages"
        :total="spotsStore.total"
        :has-prev="spotsStore.hasPrev"
        :has-next="spotsStore.hasNext"
        @select-spot="handleSelectSpot"
        @reload="spotsStore.reload"
        @change-view="spotsStore.setViewMode"
        @change-search="spotsStore.setSearchQuery"
        @change-category="spotsStore.setCategoryFilter"
        @change-tag="spotsStore.setTagFilter"
        @toggle-distance="spotsStore.setDistanceEnabled"
        @change-distance="spotsStore.setMaxDistanceKm"
        @use-my-location="handleUseMyLocation"
        @toggle-owner-only="spotsStore.setOwnerOnly"
        @clear-filters="spotsStore.resetFilters"
        @prev-page="spotsStore.prevPage"
        @next-page="spotsStore.nextPage"
      />

      <MapView
        :spots="spotsStore.filteredSpots"
        :loading="spotsStore.loading"
        :error="spotsStore.error"
        :selected-spot="selectedSpot"
        @select-spot="handleSelectSpot"
      />
    </main>

    <div v-if="showLoginModal" class="auth-modal-backdrop" @click.self="closeLoginModal">
      <section class="auth-modal" role="dialog" aria-modal="true" :aria-label="authMode === 'register' ? 'Crear cuenta' : 'Iniciar sesión'">
        <h2>{{ authMode === 'register' ? 'Crear cuenta' : 'Iniciar sesión' }}</h2>
        <p class="auth-subtitle">
          {{ authMode === 'register' ? 'Crea cuenta local para empezar a publicar spots.' : 'Usa la misma cuenta que tenías en el frontend anterior.' }}
        </p>

        <form class="auth-form" @submit.prevent="handleAuthSubmit">
          <template v-if="authMode === 'register'">
            <label for="register-name" class="auth-label">Nombre</label>
            <input id="register-name" v-model="registerName" :class="['input', { 'input--error': authFieldErrors.name }]" type="text" autocomplete="name" placeholder="Tu nombre o alias">
            <small v-if="authFieldErrors.name" class="field-error">{{ authFieldErrors.name }}</small>
          </template>

          <label for="login-email" class="auth-label">Email</label>
          <input id="login-email" v-model="loginEmail" :class="['input', { 'input--error': authFieldErrors.email }]" type="email" autocomplete="email" placeholder="tu@email.com">
          <small v-if="authFieldErrors.email" class="field-error">{{ authFieldErrors.email }}</small>

          <label for="login-password" class="auth-label">Contraseña</label>
          <input id="login-password" v-model="loginPassword" :class="['input', { 'input--error': authFieldErrors.password }]" type="password" :autocomplete="authMode === 'register' ? 'new-password' : 'current-password'" placeholder="******">
          <small v-if="authFieldErrors.password" class="field-error">{{ authFieldErrors.password }}</small>

          <p v-if="authError" class="auth-error">{{ authError }}</p>
          <button class="auth-switch" type="button" @click="setAuthMode(authMode === 'register' ? 'login' : 'register')">
            {{ authMode === 'register' ? 'Ya tengo cuenta, quiero iniciar sesión' : 'No tengo cuenta, quiero registrarme' }}
          </button>

          <div v-if="oauthProviders.length > 0" class="oauth-block">
            <span class="oauth-title">O continuar con</span>
            <div class="oauth-grid">
              <button
                v-for="provider in oauthProviders"
                :key="provider"
                class="topbar-btn oauth-btn"
                type="button"
                :disabled="authStore.oauthLoading"
                @click="handleOAuthLogin(provider)"
              >
                <span>{{ oauthProviderIcons[provider] || '🔐' }}</span>
                <span>{{ oauthProviderLabels[provider] || provider }}</span>
              </button>
            </div>
          </div>

          <div class="auth-actions">
            <button class="topbar-btn" type="button" @click="closeLoginModal">Cancelar</button>
            <button class="topbar-btn topbar-btn--primary" type="submit" :disabled="authStore.loading">
              {{ authStore.loading ? (authMode === 'register' ? 'Creando…' : 'Entrando…') : (authMode === 'register' ? 'Crear cuenta' : 'Entrar') }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <div v-if="showCreateSpotModal" class="auth-modal-backdrop" @click.self="closeCreateSpotModal">
      <section class="auth-modal auth-modal--wide" role="dialog" aria-modal="true" aria-label="Crear spot">
        <h2>Crear nuevo spot</h2>
        <p class="auth-subtitle">Paridad con legacy: título, coordenadas, categoría, tags y hasta 2 imágenes.</p>

        <form class="auth-form" @submit.prevent="handleCreateSpot">
          <label class="auth-label" for="spot-title">Título</label>
          <input id="spot-title" v-model="newSpot.title" :class="['input', { 'input--error': createSpotFieldErrors.title }]" type="text" required minlength="3">
          <small v-if="createSpotFieldErrors.title" class="field-error">{{ createSpotFieldErrors.title }}</small>

          <label class="auth-label" for="spot-description">Descripción</label>
          <textarea id="spot-description" v-model="newSpot.description" class="input" rows="3"></textarea>

          <label class="auth-label" for="spot-category">Categoría</label>
          <input id="spot-category" v-model="newSpot.category" class="input" type="text" placeholder="parque, playa, café...">

          <label class="auth-label" for="spot-tags">Tags (coma separada)</label>
          <input id="spot-tags" v-model="newSpot.tags" class="input" type="text" placeholder="familia, atardecer, foto">

          <div class="row">
            <div>
              <label class="auth-label" for="spot-lat">Latitud</label>
              <input id="spot-lat" v-model="newSpot.lat" :class="['input', { 'input--error': createSpotFieldErrors.lat }]" type="number" step="0.000001" required>
              <small v-if="createSpotFieldErrors.lat" class="field-error">{{ createSpotFieldErrors.lat }}</small>
            </div>
            <div>
              <label class="auth-label" for="spot-lng">Longitud</label>
              <input id="spot-lng" v-model="newSpot.lng" :class="['input', { 'input--error': createSpotFieldErrors.lng }]" type="number" step="0.000001" required>
              <small v-if="createSpotFieldErrors.lng" class="field-error">{{ createSpotFieldErrors.lng }}</small>
            </div>
          </div>

          <div class="spot-location-actions">
            <button class="topbar-btn" type="button" :disabled="locatingSpotCoords" @click="useCurrentCoordinatesForForm">
              {{ locatingSpotCoords ? 'Obteniendo ubicación…' : '📍 Usar mi ubicación' }}
            </button>
            <button class="topbar-btn" type="button" @click="toggleCreateSpotMapPicker">
              {{ showCreateSpotMapPicker ? 'Ocultar mapa selector' : '🗺️ Seleccionar en el mapa' }}
            </button>
          </div>

          <div v-if="showCreateSpotMapPicker" class="spot-map-picker-box">
            <p class="spot-map-picker-help">Haz click en el mapa para fijar el marcador y rellenar latitud/longitud.</p>
            <div ref="createSpotMapRoot" class="spot-map-picker" aria-label="Selector de ubicación para el spot"></div>
          </div>

          <label class="auth-label" for="spot-image-1">Imagen 1 (obligatoria al menos una)</label>
          <input id="spot-image-1" class="input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" @change="setSpotImage(1, $event)">

          <label class="auth-label" for="spot-image-2">Imagen 2 (opcional)</label>
          <input id="spot-image-2" class="input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" @change="setSpotImage(2, $event)">
          <small v-if="createSpotFieldErrors.images" class="field-error">{{ createSpotFieldErrors.images }}</small>

          <p v-if="createSpotError" class="auth-error">{{ createSpotError }}</p>

          <div class="auth-actions">
            <button class="topbar-btn" type="button" @click="closeCreateSpotModal">Cancelar</button>
            <button class="topbar-btn topbar-btn--primary" type="submit" :disabled="creatingSpot">
              {{ creatingSpot ? 'Creando…' : 'Crear Spot' }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <div class="toast-stack" aria-live="polite" aria-atomic="true">
      <div v-if="uiToast.visible" class="toast-msg" :class="`toast-msg--${uiToast.type}`">
        {{ uiToast.message }}
      </div>
    </div>
  </div>
</template>
