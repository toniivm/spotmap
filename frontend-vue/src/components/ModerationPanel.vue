<script setup>
import { computed, reactive, ref } from 'vue';
import { useModerationStore } from '../stores/moderation';

const moderationStore = useModerationStore();

const totalPending = computed(() => moderationStore.totalPending || 0);
const pendingSpots = computed(() => moderationStore.pendingSpots || []);
const editingSpotId = ref(null);
const savingEdit = ref(false);
const editError = ref('');
const editForm = reactive({
  title: '',
  description: '',
  category: '',
  tags: '',
  lat: '',
  lng: '',
  image1: null,
  image2: null,
});

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

function getImagePreview(spot, index) {
  if (index === 1) return String(spot?.image_path || spot?.imagePath || '').trim();
  return String(spot?.image_path_2 || spot?.imagePath2 || '').trim();
}

function openEdit(spot) {
  const lat = Number(spot?.lat);
  const lng = Number(spot?.lng);
  editingSpotId.value = spot.id;
  editError.value = '';
  editForm.title = String(spot?.title || '');
  editForm.description = String(spot?.description || '');
  editForm.category = String(spot?.category || '');
  editForm.tags = Array.isArray(spot?.tags) ? spot.tags.join(', ') : String(spot?.tags || '');
  editForm.lat = Number.isFinite(lat) ? lat.toFixed(6) : '';
  editForm.lng = Number.isFinite(lng) ? lng.toFixed(6) : '';
  editForm.image1 = null;
  editForm.image2 = null;
}

function closeEdit() {
  editingSpotId.value = null;
  editError.value = '';
  editForm.image1 = null;
  editForm.image2 = null;
}

function setImage(index, event) {
  const file = event?.target?.files?.[0] || null;
  if (index === 1) editForm.image1 = file;
  if (index === 2) editForm.image2 = file;
}

function validateEditForm() {
  const title = String(editForm.title || '').trim();
  const lat = Number(editForm.lat);
  const lng = Number(editForm.lng);

  if (!title) {
    editError.value = 'El titulo es obligatorio';
    return false;
  }
  if (!Number.isFinite(lat) || lat < -90 || lat > 90) {
    editError.value = 'Latitud invalida';
    return false;
  }
  if (!Number.isFinite(lng) || lng < -180 || lng > 180) {
    editError.value = 'Longitud invalida';
    return false;
  }
  return true;
}

async function saveEdit(spotId) {
  editError.value = '';
  if (!validateEditForm()) {
    return;
  }

  const form = new FormData();
  form.append('title', String(editForm.title || '').trim());
  form.append('description', String(editForm.description || '').trim());
  form.append('category', String(editForm.category || '').trim());
  form.append('lat', String(editForm.lat || '').trim());
  form.append('lng', String(editForm.lng || '').trim());

  const tags = String(editForm.tags || '')
    .split(',')
    .map((tag) => tag.trim())
    .filter(Boolean);
  form.append('tags', JSON.stringify(tags));

  if (editForm.image1 instanceof File) form.append('image1', editForm.image1);
  if (editForm.image2 instanceof File) form.append('image2', editForm.image2);

  savingEdit.value = true;
  try {
    await moderationStore.updatePending(spotId, form);
    closeEdit();
  } catch (err) {
    editError.value = err instanceof Error ? err.message : 'No se pudo guardar la edicion';
  } finally {
    savingEdit.value = false;
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
          <div class="mod-item-main" v-if="editingSpotId !== spot.id">
            <strong>{{ spot.title }}</strong>
            <small>{{ shortText(spot.description) }}</small>
          </div>

          <form v-else class="mod-edit" @submit.prevent="saveEdit(spot.id)">
            <label>Titulo</label>
            <input v-model="editForm.title" class="mod-input" type="text" required>

            <label>Descripcion</label>
            <textarea v-model="editForm.description" class="mod-input" rows="2"></textarea>

            <label>Categoria</label>
            <input v-model="editForm.category" class="mod-input" type="text">

            <label>Tags (coma separada)</label>
            <input v-model="editForm.tags" class="mod-input" type="text">

            <div class="mod-coords">
              <div>
                <label>Lat</label>
                <input v-model="editForm.lat" class="mod-input" type="number" step="0.000001" required>
              </div>
              <div>
                <label>Lng</label>
                <input v-model="editForm.lng" class="mod-input" type="number" step="0.000001" required>
              </div>
            </div>

            <div class="mod-preview-row">
              <small v-if="getImagePreview(spot, 1)">Img1 actual: {{ getImagePreview(spot, 1) }}</small>
              <small v-if="getImagePreview(spot, 2)">Img2 actual: {{ getImagePreview(spot, 2) }}</small>
            </div>

            <label>Reemplazar imagen 1</label>
            <input class="mod-input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" @change="setImage(1, $event)">

            <label>Reemplazar imagen 2</label>
            <input class="mod-input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" @change="setImage(2, $event)">

            <p v-if="editError" class="mod-state mod-state--error">{{ editError }}</p>

            <div class="mod-item-actions">
              <button class="mod-action" type="button" :disabled="savingEdit || moderationStore.actionLoading" @click="closeEdit">Cancelar</button>
              <button class="mod-action mod-action--ok" type="submit" :disabled="savingEdit || moderationStore.actionLoading">
                {{ savingEdit ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </form>

          <div v-if="editingSpotId !== spot.id" class="mod-item-actions">
            <button
              class="mod-action"
              type="button"
              :disabled="moderationStore.actionLoading"
              @click="openEdit(spot)"
            >
              Editar
            </button>
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
  z-index: 2600;
}
.mod-state { margin: 0; color: #d1d5db; font-size: 13px; }
.mod-state--error { color: #fca5a5; }
.mod-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.mod-item { border: 1px solid #1f2937; border-radius: 10px; padding: 8px; }
.mod-item-main { display: grid; gap: 4px; }
.mod-item-main small { color: #9ca3af; }
.mod-edit { display: grid; gap: 6px; margin-bottom: 8px; }
.mod-edit label { font-size: 12px; color: #cbd5e1; }
.mod-input {
  width: 100%;
  background: #0b1220;
  color: #f8fafc;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 6px 8px;
  font-size: 12px;
}
.mod-coords { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
.mod-preview-row { display: grid; gap: 2px; }
.mod-preview-row small {
  color: #93c5fd;
  font-size: 11px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
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
