function normalizeTags(tags) {
  if (!tags) return [];
  if (Array.isArray(tags)) return tags.filter(Boolean).map((tag) => String(tag).trim()).filter(Boolean);

  if (typeof tags === 'string') {
    const trimmed = tags.trim();
    if (!trimmed) return [];

    try {
      const parsed = JSON.parse(trimmed);
      if (Array.isArray(parsed)) {
        return parsed.filter(Boolean).map((tag) => String(tag).trim()).filter(Boolean);
      }
    } catch {
      return trimmed.split(',').map((tag) => tag.trim()).filter(Boolean);
    }
  }

  return [];
}

export function normalizeSpot(raw = {}) {
  const lat = Number(raw.lat);
  const lng = Number(raw.lng);
  return {
    id: raw.id,
    title: String(raw.title || 'Sin título'),
    description: String(raw.description || ''),
    category: String(raw.category || ''),
    tags: normalizeTags(raw.tags),
    lat: Number.isFinite(lat) ? lat : null,
    lng: Number.isFinite(lng) ? lng : null,
    userId: raw.user_id || raw.userId || '',
    imagePath: raw.image_path || raw.imagePath || '',
    createdAt: raw.created_at || raw.createdAt || '',
    rating: Number(raw.rating || 0),
    ratingCount: Number(raw.rating_count || raw.ratingCount || 0),
  };
}

export function filterValidCoords(spots = []) {
  return spots.filter((spot) => Number.isFinite(spot?.lat) && Number.isFinite(spot?.lng));
}
