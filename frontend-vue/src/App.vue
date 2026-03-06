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
import { useModalA11y } from './composables/useModalA11y';

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
const recoveryPassword = ref('');
const recoveryPasswordConfirm = ref('');
const authError = ref('');
const authStatusMessage = ref('');
const authFieldErrors = ref({
  name: '',
  email: '',
  password: '',
  passwordConfirm: '',
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
const appContentRef = ref(null);
const loginModalRef = ref(null);
const createSpotModalRef = ref(null);
const mobileSidebarOpen = ref(false);
const mobileMapFocus = ref(false);
const isCompactViewport = ref(false);
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
const isAdmin = computed(() => authStore.isAdmin);
const isModerator = computed(() => authStore.isModerator);
const currentUsername = computed(() => authStore.username || 'Usuario');
const currentRoleLabel = computed(() => authStore.roleLabel || 'Usuario');
const oauthProviders = computed(() => authStore.oauthProviders || []);
const hasUserLocation = computed(() => !!spotsStore.userLocation);
const totalVisibleSpots = computed(() => spotsStore.filteredSpots.length || 0);
const totalCategories = computed(() => spotsStore.availableCategories.length || 0);
const totalTags = computed(() => spotsStore.availableTags.length || 0);
const { captureLastFocusedElement, isAnyModalOpen } = useModalA11y({
  showLoginModal,
  showCreateSpotModal,
  loginModalRef,
  createSpotModalRef,
  closeLoginModal,
  closeCreateSpotModal,
});

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

function handleSelectSpotFromSidebar(spotId) {
  selectedSpotId.value = spotId;
  if (isCompactViewport.value) {
    mobileSidebarOpen.value = false;
  }
}

function syncViewportState() {
  isCompactViewport.value = window.matchMedia('(max-width: 991px)').matches;
  if (!isCompactViewport.value) {
    mobileSidebarOpen.value = false;
    mobileMapFocus.value = false;
  }
}

function toggleMobileSidebar() {
  mobileSidebarOpen.value = !mobileSidebarOpen.value;
  if (mobileSidebarOpen.value) {
    mobileMapFocus.value = false;
  }
}

function toggleMobileMapFocus() {
  mobileMapFocus.value = !mobileMapFocus.value;
  if (mobileMapFocus.value) {
    mobileSidebarOpen.value = false;
  }
}

function clearAuthValidationErrors() {
  authFieldErrors.value = {
    name: '',
    email: '',
    password: '',
    passwordConfirm: '',
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
  const password = authMode.value === 'reset'
    ? String(recoveryPassword.value || '')
    : String(loginPassword.value || '');
  const name = String(registerName.value || '').trim();
  const passwordConfirm = String(recoveryPasswordConfirm.value || '');

  if (authMode.value !== 'reset') {
    if (!email) {
      authFieldErrors.value.email = 'El email es obligatorio';
    } else if (!isValidEmail(email)) {
      authFieldErrors.value.email = 'El email no tiene formato válido';
    }
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

  if (authMode.value === 'reset') {
    if (!passwordConfirm) {
      authFieldErrors.value.passwordConfirm = 'Confirma la contrasena';
    } else if (password !== passwordConfirm) {
      authFieldErrors.value.passwordConfirm = 'Las contrasenas no coinciden';
    }
  }

  return !authFieldErrors.value.name
    && !authFieldErrors.value.email
    && !authFieldErrors.value.password
    && !authFieldErrors.value.passwordConfirm;
}

function isRecoveryCallback() {
  const rawHash = String(window.location.hash || '').replace(/^#/, '');
  if (!rawHash) return false;
  const params = new URLSearchParams(rawHash);
  return String(params.get('type') || '').toLowerCase() === 'recovery';
}

function clearRecoveryHash() {
  if (!window.location.hash) return;
  window.history.replaceState({}, document.title, window.location.pathname + window.location.search);
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
  captureLastFocusedElement();
  authMode.value = 'login';
  authError.value = '';
  authStatusMessage.value = '';
  clearAuthValidationErrors();
  showLoginModal.value = true;
}

function closeLoginModal() {
  showLoginModal.value = false;
  authError.value = '';
  authStatusMessage.value = '';
  loginPassword.value = '';
  recoveryPassword.value = '';
  recoveryPasswordConfirm.value = '';
  clearAuthValidationErrors();
}

function setAuthMode(mode) {
  authMode.value = mode;
  authError.value = '';
  authStatusMessage.value = '';
  loginPassword.value = '';
  recoveryPassword.value = '';
  recoveryPasswordConfirm.value = '';
  clearAuthValidationErrors();
}

async function handleAuthSubmit() {
  authError.value = '';
  authStatusMessage.value = '';
  if (!validateAuthForm()) {
    authError.value = 'Revisa los campos obligatorios';
    return;
  }

  try {
    if (authMode.value === 'register') {
      await authStore.register(registerName.value.trim(), loginEmail.value, loginPassword.value);
    } else if (authMode.value === 'reset') {
      await authStore.updatePasswordFromRecovery(recoveryPassword.value);
      authStatusMessage.value = 'Contrasena actualizada. Ya puedes iniciar sesion.';
      showToast(authStatusMessage.value, 'success');
      setAuthMode('login');
      recoveryPassword.value = '';
      recoveryPasswordConfirm.value = '';
      clearRecoveryHash();
      return;
    } else {
      await authStore.login(loginEmail.value, loginPassword.value);
    }
    closeLoginModal();
    loginPassword.value = '';
    showToast(authMode.value === 'register' ? 'Cuenta creada correctamente' : 'Sesión iniciada', 'success');
  } catch (err) {
    const message = err instanceof Error
      ? err.message
      : authMode.value === 'register'
        ? 'No se pudo crear la cuenta'
        : 'No se pudo iniciar sesión';

    if (authMode.value === 'register' && message.toLowerCase().includes('revisa tu email')) {
      authStatusMessage.value = message;
      authMode.value = 'login';
      loginPassword.value = '';
      showToast('Cuenta creada. Revisa tu correo para verificarla.', 'info');
      return;
    }

    authError.value = message;
    showToast(authError.value, 'error');
  }
}

async function handleForgotPassword() {
  authError.value = '';
  authStatusMessage.value = '';
  const email = String(loginEmail.value || '').trim();
  if (!isValidEmail(email)) {
    authError.value = 'Escribe un email valido para recuperar la cuenta';
    return;
  }

  try {
    await authStore.sendPasswordReset(email);
    authStatusMessage.value = 'Te hemos enviado un correo para restablecer la contrasena.';
    showToast(authStatusMessage.value, 'success');
  } catch (err) {
    authError.value = err instanceof Error ? err.message : 'No se pudo enviar la recuperacion';
    showToast(authError.value, 'error');
  }
}

async function handleResendVerification() {
  authError.value = '';
  authStatusMessage.value = '';
  const email = String(loginEmail.value || '').trim();
  if (!isValidEmail(email)) {
    authError.value = 'Escribe un email valido para reenviar la verificacion';
    return;
  }

  try {
    await authStore.sendVerificationEmail(email);
    authStatusMessage.value = 'Correo de verificacion reenviado. Revisa tu bandeja.';
    showToast(authStatusMessage.value, 'success');
  } catch (err) {
    authError.value = err instanceof Error ? err.message : 'No se pudo reenviar la verificacion';
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
  captureLastFocusedElement();
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

    const createdPayload = await apiFetch('/spots', { method: 'POST', body: form, token });
    const createdSpot = createdPayload?.data ?? createdPayload ?? {};
    await spotsStore.reload();
    if (notificationsStore.supported) {
      await notificationsStore.load();
    }
    closeCreateSpotModal();
    if (String(createdSpot.status || '') === 'pending') {
      showToast('Spot enviado: esta siendo revisado por moderacion.', 'info');
    } else {
      showToast('Spot creado correctamente', 'success');
    }
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

async function handleDeleteSpot(spot) {
  const spotId = Number(spot?.id || 0);
  if (!spotId) return;

  const ok = window.confirm(`¿Eliminar spot "${spot?.title || 'sin título'}"?`);
  if (!ok) return;

  try {
    await apiFetch(`/spots/${spotId}`, { method: 'DELETE' });
    if (String(selectedSpotId.value) === String(spotId)) {
      selectedSpotId.value = null;
    }
    await spotsStore.reload();
    showToast('Spot eliminado', 'success');
  } catch (err) {
    showToast(err instanceof Error ? err.message : 'No se pudo eliminar el spot', 'error');
  }
}

async function handleEditSpot(spot) {
  const spotId = Number(spot?.id || 0);
  if (!spotId) return;

  const title = window.prompt('Nuevo título:', String(spot?.title || '').trim());
  if (title === null) return;

  const description = window.prompt('Nueva descripción:', String(spot?.description || '').trim());
  if (description === null) return;

  const category = window.prompt('Nueva categoría:', String(spot?.category || '').trim());
  if (category === null) return;

  try {
    await apiFetch(`/spots/${spotId}`, {
      method: 'PATCH',
      body: {
        title,
        description,
        category,
      },
    });
    await spotsStore.reload();
    showToast('Spot actualizado', 'success');
  } catch (err) {
    showToast(err instanceof Error ? err.message : 'No se pudo actualizar el spot', 'error');
  }
}

onMounted(() => {
  syncViewportState();
  window.addEventListener('resize', syncViewportState, { passive: true });
  authStore.init();
  spotsStore.loadSpots();

  if (isRecoveryCallback()) {
    authMode.value = 'reset';
    showLoginModal.value = true;
    authStatusMessage.value = 'Define una nueva contrasena para tu cuenta.';
    authError.value = '';
    clearAuthValidationErrors();
  }
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportState);
  destroyCreateSpotMap();
  notificationsStore.stopPolling();
  if (toastTimer) {
    clearTimeout(toastTimer);
  }
});
</script>

<template>
  <div class="app-shell" :class="{ 'app-shell--map-focus': mobileMapFocus }">
    <div ref="appContentRef" :inert="isAnyModalOpen" :aria-hidden="isAnyModalOpen ? 'true' : 'false'">
      <header class="topbar">
        <div class="brand-wrap">
          <div class="brand">📸 SpotMap</div>
          <span class="brand-subtitle">Mapa colaborativo para descubrir y compartir spots</span>
        </div>
        <div class="topbar-actions">
          <button class="topbar-btn only-mobile" type="button" @click="toggleMobileSidebar">
            {{ mobileSidebarOpen ? 'Cerrar filtros' : 'Filtros' }}
          </button>
          <button class="topbar-btn only-mobile" type="button" @click="toggleMobileMapFocus">
            {{ mobileMapFocus ? 'Vista normal' : 'Solo mapa' }}
          </button>
          <ModerationPanel v-if="isAuthenticated && isModerator" />
          <button v-if="isAuthenticated" class="topbar-btn topbar-btn--primary" type="button" @click="openCreateSpotModal">+ Añadir Spot</button>
          <NotificationsDropdown v-if="isAuthenticated" />
          <span v-if="isAuthenticated" class="meta">Hola, {{ currentUsername }}</span>
          <span v-if="isAuthenticated" class="role-pill">{{ currentRoleLabel }}</span>
          <button v-if="isAuthenticated" class="topbar-btn" type="button" @click="handleLogout">Cerrar sesión</button>
          <button v-else class="topbar-btn" type="button" @click="openLoginModal">Iniciar sesión</button>
        </div>
      </header>

      <section class="kpi-strip" aria-label="Resumen rápido">
        <article class="kpi-card">
          <span>Spots visibles</span>
          <strong>{{ totalVisibleSpots }}</strong>
        </article>
        <article class="kpi-card">
          <span>Categorías activas</span>
          <strong>{{ totalCategories }}</strong>
        </article>
        <article class="kpi-card">
          <span>Etiquetas detectadas</span>
          <strong>{{ totalTags }}</strong>
        </article>
      </section>

      <main class="layout" :class="{ 'layout--sidebar-open': mobileSidebarOpen, 'layout--map-focus': mobileMapFocus }">
        <button
          v-if="isCompactViewport && mobileSidebarOpen"
          type="button"
          class="sidebar-backdrop"
          aria-label="Cerrar panel de filtros"
          @click="mobileSidebarOpen = false"
        ></button>

        <div class="sidebar-shell" :class="{ 'sidebar-shell--open': mobileSidebarOpen }">
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
            :can-manage-spots="isAuthenticated && (isAdmin || isModerator)"
            :page="spotsStore.page"
            :pages="spotsStore.pages"
            :total="spotsStore.total"
            :has-prev="spotsStore.hasPrev"
            :has-next="spotsStore.hasNext"
            @select-spot="handleSelectSpotFromSidebar"
            @reload="spotsStore.reload"
            @change-view="spotsStore.setViewMode"
            @change-search="spotsStore.setSearchQuery"
            @change-category="spotsStore.setCategoryFilter"
            @change-tag="spotsStore.setTagFilter"
            @toggle-distance="spotsStore.setDistanceEnabled"
            @change-distance="spotsStore.setMaxDistanceKm"
            @use-my-location="handleUseMyLocation"
            @toggle-owner-only="spotsStore.setOwnerOnly"
            @edit-spot="handleEditSpot"
            @delete-spot="handleDeleteSpot"
            @clear-filters="spotsStore.resetFilters"
            @prev-page="spotsStore.prevPage"
            @next-page="spotsStore.nextPage"
          />
        </div>

        <div class="map-shell">
          <MapView
            :spots="spotsStore.filteredSpots"
            :loading="spotsStore.loading"
            :error="spotsStore.error"
            :selected-spot="selectedSpot"
            @select-spot="handleSelectSpot"
          />
        </div>
      </main>
    </div>

    <div v-if="showLoginModal" class="auth-modal-backdrop" @click.self="closeLoginModal">
      <section ref="loginModalRef" class="auth-modal" role="dialog" aria-modal="true" tabindex="-1" :aria-label="authMode === 'register' ? 'Crear cuenta' : (authMode === 'reset' ? 'Actualizar contrasena' : 'Iniciar sesión')">
        <h2>{{ authMode === 'register' ? 'Crear cuenta' : (authMode === 'reset' ? 'Actualizar contrasena' : 'Iniciar sesión') }}</h2>
        <p class="auth-subtitle">
          {{ authMode === 'register' ? 'Crea tu cuenta para empezar a publicar spots.' : (authMode === 'reset' ? 'Introduce una nueva contrasena para recuperar tu cuenta.' : 'Accede para guardar y gestionar tus spots.') }}
        </p>

        <form class="auth-form" @submit.prevent="handleAuthSubmit">
          <template v-if="authMode === 'register'">
            <label for="register-name" class="auth-label">Nombre</label>
            <input id="register-name" v-model="registerName" :class="['input', { 'input--error': authFieldErrors.name }]" type="text" autocomplete="name" placeholder="Tu nombre o alias">
            <small v-if="authFieldErrors.name" class="field-error">{{ authFieldErrors.name }}</small>
          </template>

          <template v-if="authMode !== 'reset'">
            <label for="login-email" class="auth-label">Email</label>
            <input id="login-email" v-model="loginEmail" :class="['input', { 'input--error': authFieldErrors.email }]" type="email" autocomplete="email" placeholder="tu@email.com">
            <small v-if="authFieldErrors.email" class="field-error">{{ authFieldErrors.email }}</small>

            <label for="login-password" class="auth-label">Contraseña</label>
            <input id="login-password" v-model="loginPassword" :class="['input', { 'input--error': authFieldErrors.password }]" type="password" :autocomplete="authMode === 'register' ? 'new-password' : 'current-password'" placeholder="******">
            <small v-if="authFieldErrors.password" class="field-error">{{ authFieldErrors.password }}</small>
          </template>

          <template v-else>
            <label for="recovery-password" class="auth-label">Nueva contrasena</label>
            <input id="recovery-password" v-model="recoveryPassword" :class="['input', { 'input--error': authFieldErrors.password }]" type="password" autocomplete="new-password" placeholder="******">
            <small v-if="authFieldErrors.password" class="field-error">{{ authFieldErrors.password }}</small>

            <label for="recovery-password-confirm" class="auth-label">Confirmar contrasena</label>
            <input id="recovery-password-confirm" v-model="recoveryPasswordConfirm" :class="['input', { 'input--error': authFieldErrors.passwordConfirm }]" type="password" autocomplete="new-password" placeholder="******">
            <small v-if="authFieldErrors.passwordConfirm" class="field-error">{{ authFieldErrors.passwordConfirm }}</small>
          </template>

          <div v-if="authMode === 'login'" class="auth-inline-actions">
            <button class="auth-switch" type="button" @click="handleForgotPassword">Olvide mi contrasena</button>
            <button class="auth-switch" type="button" @click="handleResendVerification">Reenviar verificacion</button>
          </div>

          <p v-if="authError" class="auth-error">{{ authError }}</p>
          <p v-if="authStatusMessage" class="auth-success">{{ authStatusMessage }}</p>
          <button v-if="authMode === 'register'" class="auth-switch" type="button" @click="setAuthMode('login')">
            Ya tengo cuenta, quiero iniciar sesion
          </button>
          <button v-else-if="authMode === 'login'" class="auth-switch" type="button" @click="setAuthMode('register')">
            No tengo cuenta, quiero registrarme
          </button>
          <button v-else class="auth-switch" type="button" @click="setAuthMode('login')">
            Volver al inicio de sesion
          </button>

          <div v-if="oauthProviders.length > 0 && authMode !== 'reset'" class="oauth-block">
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
              {{ authStore.loading ? (authMode === 'register' ? 'Creando…' : (authMode === 'reset' ? 'Actualizando…' : 'Entrando…')) : (authMode === 'register' ? 'Crear cuenta' : (authMode === 'reset' ? 'Guardar nueva contrasena' : 'Entrar')) }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <div v-if="showCreateSpotModal" class="auth-modal-backdrop" @click.self="closeCreateSpotModal">
      <section ref="createSpotModalRef" class="auth-modal auth-modal--wide" role="dialog" aria-modal="true" tabindex="-1" aria-label="Crear spot">
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
