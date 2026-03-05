<script setup>
import { computed, ref } from 'vue';
import { useNotificationsStore } from '../stores/notifications';

const notificationsStore = useNotificationsStore();
const open = ref(false);

const items = computed(() => notificationsStore.items || []);
const unreadCount = computed(() => notificationsStore.unreadCount || 0);

function toggle() {
  open.value = !open.value;
  if (open.value) {
    notificationsStore.load();
  }
}

function close() {
  open.value = false;
}

function relativeDate(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  const diff = Date.now() - date.getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'ahora';
  if (mins < 60) return `hace ${mins}m`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `hace ${hours}h`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `hace ${days}d`;
  return date.toLocaleDateString('es-ES');
}
</script>

<template>
  <div class="notif-wrapper">
    <button class="topbar-btn notif-btn" type="button" @click="toggle">
      🔔
      <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <div v-if="open" class="notif-dropdown" role="menu">
      <div class="notif-header">
        <strong>Notificaciones</strong>
        <button class="notif-link" type="button" @click="notificationsStore.markAllAsRead">Marcar leídas</button>
      </div>

      <div v-if="notificationsStore.loading" class="notif-state">Cargando...</div>
      <div v-else-if="items.length === 0" class="notif-state">Sin notificaciones</div>

      <ul v-else class="notif-list">
        <li v-for="item in items" :key="item.id" :class="['notif-item', { 'notif-item--unread': !item.isRead }]">
          <button class="notif-item-btn" type="button" @click="notificationsStore.markAsRead(item.id)">
            <span class="notif-title">{{ item.title }}</span>
            <span class="notif-message">{{ item.message }}</span>
            <small class="notif-time">{{ relativeDate(item.createdAt) }}</small>
          </button>
          <button class="notif-remove" type="button" @click="notificationsStore.remove(item.id)">×</button>
        </li>
      </ul>

      <div class="notif-footer">
        <button class="notif-link" type="button" @click="close">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.notif-wrapper { position: relative; }
.notif-btn { position: relative; }
.notif-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 11px;
  line-height: 18px;
  text-align: center;
  padding: 0 4px;
}
.notif-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  width: 320px;
  max-height: 360px;
  overflow: auto;
  background: #111827;
  border: 1px solid #374151;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,.35);
  z-index: 30;
}
.notif-header,
.notif-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  border-bottom: 1px solid #1f2937;
}
.notif-footer { border-top: 1px solid #1f2937; border-bottom: 0; }
.notif-link {
  background: transparent;
  border: 0;
  color: #60a5fa;
  cursor: pointer;
  font-size: 12px;
}
.notif-state { padding: 16px 12px; color: #9ca3af; }
.notif-list { list-style: none; margin: 0; padding: 0; }
.notif-item {
  display: flex;
  gap: 8px;
  padding: 8px 12px;
  border-top: 1px solid #1f2937;
}
.notif-item--unread { background: rgba(59,130,246,.15); }
.notif-item-btn {
  flex: 1;
  text-align: left;
  background: transparent;
  border: 0;
  color: inherit;
  cursor: pointer;
}
.notif-title { display: block; font-weight: 600; font-size: 13px; }
.notif-message { display: block; color: #d1d5db; font-size: 12px; margin-top: 2px; }
.notif-time { display: block; color: #9ca3af; margin-top: 4px; }
.notif-remove {
  background: transparent;
  border: 0;
  color: #9ca3af;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
}
</style>
