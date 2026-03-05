import { apiFetch } from './api';

function toData(payload) {
  return payload?.data ?? payload ?? {};
}

export function isModerationUnsupported(errorValue) {
  const message = String(errorValue?.message || errorValue || '').toLowerCase();
  return message.includes('moderation requires supabase')
    || message.includes('not supported')
    || message.includes('forbidden')
    || message.includes('unauthorized');
}

export async function fetchPendingSpots({ token, page = 1, limit = 50 } = {}) {
  const data = toData(await apiFetch(`/admin/pending?page=${page}&limit=${limit}`, { token }));
  const spots = Array.isArray(data?.spots) ? data.spots : [];
  const total = Number(data?.total) || spots.length;
  return { spots, total };
}

export async function approvePendingSpot(spotId, { token } = {}) {
  await apiFetch(`/admin/spots/${spotId}/approve`, { method: 'POST', token });
  return true;
}

export async function rejectPendingSpot(spotId, { token } = {}) {
  await apiFetch(`/admin/spots/${spotId}/reject`, { method: 'POST', token });
  return true;
}

export async function fetchModerationStats({ token } = {}) {
  const data = toData(await apiFetch('/admin/stats', { token }));
  return {
    spotsTotal: Number(data?.spotsTotal || data?.spots_total || 0),
    reportsPending: Number(data?.reportsPending || data?.reports_pending || 0),
    averageRatingGlobal: Number(data?.averageRatingGlobal || data?.average_rating_global || 0),
  };
}
