<script setup>
import { computed } from 'vue';
import { useModerationStore } from '../stores/moderation';

const moderationStore = useModerationStore();

const totalPending = computed(() => moderationStore.totalPending || 0);
const pendingSpots = computed(() => moderationStore.pendingSpots || []);

function shortText(value) {
  const text = String(value || '');
  if (text.length <= 84) return text;
  return `${text.slice(0, 84)}...`;
}

async function togglePanel() {
  if (!moderationStore.supported) return;
  if (pendingSpots.value.length === 0 && !moderationStore.loading) {
    await moderationStore.loadPending();
  }
}
</script>

<template>
  <details class="mod-panel" @toggle="togglePanel">
    <summary class="topbar-btn mod-summary">
      🛡️ Moderación
      <span v-if="totalPending > 0" class="mod-badge">{{ totalPending }}</span>
    </summary>

    <div class="mod-content">
      <p v-if="!moderationStore.supported" class="mod-state">Moderación no disponible en este backend.</p>
      <p v-else-if="moderationStore.loading" class="mod-state">Cargando pendientes...</p>
      <p v-else-if="moderationStore.error" class="mod-state mod-state--error">{{ moderationStore.error }}</p>
      <p v-else-if="pendingSpots.length === 0" class="mod-state">Sin spots pendientes.</p>

      <ul v-else class="mod-list">
        <li v-for="spot in pendingSpots" :key="spot.id" class="mod-item">
          <div class="mod-item-main">
            <strong>{{ spot.title }}</strong>
            <small>{{ shortText(spot.description) }}</small>
          </div>
          <div class="mod-item-actions">
            <button class="mod-action mod-action--ok" type="button" :disabled="moderationStore.actionLoading" @click="moderationStore.approve(spot.id)">Aprobar</button>
            <button class="mod-action mod-action--reject" type="button" :disabled="moderationStore.actionLoading" @click="moderationStore.reject(spot.id)">Rechazar</button>
          </div>
        </li>
      </ul>
    </div>
  </details>
</template>

<style scoped>
.mod-panel { position: relative; }
.mod-summary { list-style: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
.mod-summary::-webkit-details-marker { display: none; }
.mod-badge {
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #f59e0b;
  color: #111827;
  font-size: 11px;
  line-height: 18px;
  text-align: center;
  font-weight: 700;
  padding: 0 4px;
}
.mod-content {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  width: 340px;
  max-height: 360px;
  overflow: auto;
  background: #111827;
  border: 1px solid #374151;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,.35);
  padding: 10px;
  z-index: 35;
}
.mod-state { margin: 0; color: #d1d5db; font-size: 13px; }
.mod-state--error { color: #fca5a5; }
.mod-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.mod-item { border: 1px solid #1f2937; border-radius: 10px; padding: 8px; }
.mod-item-main { display: grid; gap: 4px; }
.mod-item-main small { color: #9ca3af; }
.mod-item-actions { display: flex; gap: 6px; margin-top: 8px; }
.mod-action {
  border: 0;
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 12px;
}
.mod-action--ok { background: #10b981; color: #052e1b; }
.mod-action--reject { background: #ef4444; color: #fff; }
</style>
