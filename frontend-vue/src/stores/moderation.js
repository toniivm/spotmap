import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import {
  approvePendingSpot,
  editPendingSpot,
  fetchModerationStats,
  fetchPendingSpots,
  isModerationUnsupported,
  rejectPendingSpot,
} from '../services/moderation';
import { getStoredAccessToken } from '../services/auth';

export const useModerationStore = defineStore('moderation', () => {
  const pendingSpots = ref([]);
  const totalPending = ref(0);
  const loading = ref(false);
  const actionLoading = ref(false);
  const error = ref('');
  const supported = ref(true);
  const stats = ref({
    spotsTotal: 0,
    reportsPending: 0,
    averageRatingGlobal: 0,
  });

  const hasPending = computed(() => totalPending.value > 0);

  function reset() {
    pendingSpots.value = [];
    totalPending.value = 0;
    error.value = '';
    supported.value = true;
    stats.value = {
      spotsTotal: 0,
      reportsPending: 0,
      averageRatingGlobal: 0,
    };
  }

  function handleUnsupported(errorValue) {
    if (isModerationUnsupported(errorValue)) {
      supported.value = false;
      error.value = '';
      pendingSpots.value = [];
      totalPending.value = 0;
      return true;
    }
    return false;
  }

  async function loadPending() {
    const token = getStoredAccessToken();
    if (!token) {
      pendingSpots.value = [];
      totalPending.value = 0;
      return;
    }

    loading.value = true;
    error.value = '';
    try {
      const data = await fetchPendingSpots({ token, page: 1, limit: 50 });
      pendingSpots.value = data.spots;
      totalPending.value = data.total;
      try {
        stats.value = await fetchModerationStats({ token });
      } catch {
        // Non-blocking: stats are optional in panel
      }
    } catch (err) {
      if (!handleUnsupported(err)) {
        error.value = err instanceof Error ? err.message : 'No se pudo cargar moderación';
      }
    } finally {
      loading.value = false;
    }
  }

  async function approve(spotId) {
    const token = getStoredAccessToken();
    if (!token || !spotId) return;

    actionLoading.value = true;
    error.value = '';
    try {
      await approvePendingSpot(spotId, { token });
      await loadPending();
    } catch (err) {
      if (!handleUnsupported(err)) {
        error.value = err instanceof Error ? err.message : 'No se pudo aprobar el spot';
      }
    } finally {
      actionLoading.value = false;
    }
  }

  async function reject(spotId) {
    const token = getStoredAccessToken();
    if (!token || !spotId) return;

    actionLoading.value = true;
    error.value = '';
    try {
      await rejectPendingSpot(spotId, { token });
      await loadPending();
    } catch (err) {
      if (!handleUnsupported(err)) {
        error.value = err instanceof Error ? err.message : 'No se pudo rechazar el spot';
      }
    } finally {
      actionLoading.value = false;
    }
  }

  async function updatePending(spotId, formData) {
    const token = getStoredAccessToken();
    if (!token || !spotId) return;

    actionLoading.value = true;
    error.value = '';
    try {
      await editPendingSpot(spotId, formData, { token });
      await loadPending();
    } catch (err) {
      if (!handleUnsupported(err)) {
        error.value = err instanceof Error ? err.message : 'No se pudo editar el spot pendiente';
      }
      throw err;
    } finally {
      actionLoading.value = false;
    }
  }

  return {
    pendingSpots,
    totalPending,
    loading,
    actionLoading,
    error,
    supported,
    stats,
    hasPending,
    reset,
    loadPending,
    approve,
    reject,
    updatePending,
  };
});