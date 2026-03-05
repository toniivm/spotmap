import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { apiFetch } from '../services/api';
import { getStoredAccessToken } from '../services/auth';

export const useNotificationsStore = defineStore('notifications', () => {
  const items = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);
  const error = ref('');
  const polling = ref(false);
  const supported = ref(true);
  const pollMs = 30000;
  let timer = null;

  const hasUnread = computed(() => unreadCount.value > 0);

  function isNotificationsUnsupported(errorValue) {
    const text = String(errorValue?.message || errorValue || '').toLowerCase();
    return text.includes('notifications')
      && (
        text.includes('does not exist')
        || text.includes('could not find the table')
        || text.includes('pgrst205')
        || text.includes('42p01')
      );
  }

  function normalizeItems(raw) {
    return (Array.isArray(raw) ? raw : []).map((item) => ({
      id: item.id,
      title: item.title || 'Notificación',
      message: item.message || '',
      type: item.type || 'system',
      isRead: Boolean(item.is_read ?? item.isRead),
      createdAt: item.created_at || item.createdAt || '',
    }));
  }

  async function load() {
    const token = getStoredAccessToken();
    if (!token) {
      clearForLogout();
      return;
    }

    loading.value = true;
    error.value = '';
    try {
      const payload = await apiFetch('/notifications?limit=20&unread_only=false', { token });
      const data = payload?.data ?? payload ?? {};
      items.value = normalizeItems(data.notifications || []);

      const countPayload = await apiFetch('/notifications/unread-count', { token });
      const countData = countPayload?.data ?? countPayload ?? {};
      unreadCount.value = Number(countData.count || 0);
      supported.value = true;
    } catch (err) {
      if (isNotificationsUnsupported(err)) {
        supported.value = false;
        stopPolling();
        error.value = '';
      } else {
        error.value = err instanceof Error ? err.message : 'No se pudieron cargar notificaciones';
      }
      items.value = [];
      unreadCount.value = 0;
    } finally {
      loading.value = false;
    }
  }

  function stopPolling() {
    polling.value = false;
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function startPolling() {
    if (!supported.value) return;
    stopPolling();
    polling.value = true;
    timer = setInterval(() => {
      load();
    }, pollMs);
  }

  async function initForSession() {
    await load();
    if (!supported.value) return;
    startPolling();
  }

  async function markAsRead(notificationId) {
    const token = getStoredAccessToken();
    if (!token || !notificationId) return;
    try {
      await apiFetch(`/notifications/${notificationId}/read`, { method: 'PATCH', token });
      await load();
    } catch {
      // non-blocking
    }
  }

  async function markAllAsRead() {
    const token = getStoredAccessToken();
    if (!token) return;
    try {
      await apiFetch('/notifications/mark-all-read', { method: 'POST', token });
      await load();
    } catch {
      // non-blocking
    }
  }

  async function remove(notificationId) {
    const token = getStoredAccessToken();
    if (!token || !notificationId) return;
    try {
      await apiFetch(`/notifications/${notificationId}`, { method: 'DELETE', token });
      await load();
    } catch {
      // non-blocking
    }
  }

  function clearForLogout() {
    stopPolling();
    supported.value = true;
    items.value = [];
    unreadCount.value = 0;
    error.value = '';
  }

  return {
    items,
    unreadCount,
    hasUnread,
    loading,
    error,
    polling,
    supported,
    load,
    initForSession,
    stopPolling,
    markAsRead,
    markAllAsRead,
    remove,
    clearForLogout,
  };
});
