import { getSupabaseClient, getUserRole, requireSession } from './supabase';

function parseEndpoint(endpoint) {
  const url = new URL(endpoint.startsWith('/') ? endpoint : `/${endpoint}`, 'http://localhost');
  return {
    path: url.pathname,
    searchParams: url.searchParams,
  };
}

function toInt(value, fallback) {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? Math.trunc(parsed) : fallback;
}

function splitTags(raw) {
  if (!raw) return [];
  if (Array.isArray(raw)) return raw.map((tag) => String(tag).trim()).filter(Boolean);
  if (typeof raw === 'string') {
    return raw
      .split(',')
      .map((tag) => tag.trim())
      .filter(Boolean);
  }
  return [];
}

function normalizeSpotRow(row = {}) {
  return {
    ...row,
    tags: splitTags(row.tags),
  };
}

async function ensureModeratorRole() {
  const session = await requireSession();
  const role = await getUserRole(session.user.id);
  if (!['admin', 'moderator'].includes(role)) {
    throw new Error('Forbidden');
  }
  return session;
}

async function handleGetSpots(pathSearch) {
  const supabase = getSupabaseClient();
  const page = Math.max(1, toInt(pathSearch.searchParams.get('page'), 1));
  const limit = Math.max(1, Math.min(100, toInt(pathSearch.searchParams.get('limit'), 24)));
  const category = String(pathSearch.searchParams.get('category') || '').trim();
  const tag = String(pathSearch.searchParams.get('tag') || '').trim();
  const from = (page - 1) * limit;
  const to = from + limit - 1;

  let query = supabase
    .from('spots')
    .select('*', { count: 'exact' })
    .eq('status', 'approved')
    .order('created_at', { ascending: false })
    .range(from, to);

  if (category) {
    query = query.eq('category', category);
  }

  if (tag) {
    query = query.contains('tags', [tag]);
  }

  const { data, error, count } = await query;
  if (error) throw new Error(error.message || 'No se pudieron cargar spots');

  const spots = (Array.isArray(data) ? data : []).map(normalizeSpotRow);
  const total = Number(count || spots.length);
  const pages = Math.max(1, Math.ceil(total / limit));

  return {
    data: {
      spots,
      pagination: {
        page,
        limit,
        total,
        pages,
      },
    },
  };
}

async function uploadSpotImage(file, spotId, imageNumber) {
  const supabase = getSupabaseClient();
  const extension = String(file?.name || 'jpg').split('.').pop() || 'jpg';
  const path = `spot-${spotId}-img${imageNumber}-${Date.now()}.${extension}`;
  const { error: uploadError } = await supabase.storage
    .from('spot-images')
    .upload(path, file, { upsert: false });

  if (uploadError) {
    throw new Error(uploadError.message || 'No se pudo subir la imagen');
  }

  const { data } = supabase.storage.from('spot-images').getPublicUrl(path);
  return data?.publicUrl || '';
}

async function handleCreateSpot(body) {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const role = await getUserRole(session.user.id);

  if (!(body instanceof FormData)) {
    throw new Error('Formato de creación inválido');
  }

  const title = String(body.get('title') || '').trim();
  const description = String(body.get('description') || '').trim();
  const category = String(body.get('category') || '').trim();
  const lat = Number(body.get('lat'));
  const lng = Number(body.get('lng'));
  const tagsRaw = String(body.get('tags') || '').trim();

  if (!title) throw new Error('El título es obligatorio');
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) throw new Error('Coordenadas inválidas');

  let tags = [];
  try {
    const parsed = tagsRaw ? JSON.parse(tagsRaw) : [];
    tags = splitTags(parsed);
  } catch {
    tags = splitTags(tagsRaw);
  }

  const status = ['admin', 'moderator'].includes(role) ? 'approved' : 'pending';

  const { data: created, error: createError } = await supabase
    .from('spots')
    .insert({
      user_id: session.user.id,
      title,
      description,
      category,
      tags,
      lat,
      lng,
      status,
    })
    .select('*')
    .single();

  if (createError || !created?.id) {
    throw new Error(createError?.message || 'No se pudo crear el spot');
  }

  const updates = {};
  const image1 = body.get('image1');
  const image2 = body.get('image2');

  if (image1 instanceof File) {
    updates.image_path = await uploadSpotImage(image1, created.id, 1);
  }
  if (image2 instanceof File) {
    updates.image_path_2 = await uploadSpotImage(image2, created.id, 2);
  }

  if (Object.keys(updates).length > 0) {
    const { data: updated, error: updateError } = await supabase
      .from('spots')
      .update(updates)
      .eq('id', created.id)
      .select('*')
      .single();

    if (updateError) {
      throw new Error(updateError.message || 'No se pudo actualizar imágenes del spot');
    }
    return { data: normalizeSpotRow(updated) };
  }

  return { data: normalizeSpotRow(created) };
}

async function handleGetPending(pathSearch) {
  await ensureModeratorRole();
  const supabase = getSupabaseClient();
  const page = Math.max(1, toInt(pathSearch.searchParams.get('page'), 1));
  const limit = Math.max(1, Math.min(100, toInt(pathSearch.searchParams.get('limit'), 50)));
  const from = (page - 1) * limit;
  const to = from + limit - 1;

  const { data, error, count } = await supabase
    .from('spots')
    .select('*', { count: 'exact' })
    .eq('status', 'pending')
    .order('created_at', { ascending: false })
    .range(from, to);

  if (error) throw new Error(error.message || 'No se pudieron cargar pendientes');

  const spots = (Array.isArray(data) ? data : []).map(normalizeSpotRow);
  return {
    data: {
      spots,
      total: Number(count || spots.length),
    },
  };
}

async function handleModerationAction(path, nextStatus) {
  await ensureModeratorRole();
  const supabase = getSupabaseClient();
  const match = path.match(/^\/admin\/spots\/(\d+)\/(approve|reject)$/);
  const spotId = Number(match?.[1] || 0);
  if (!spotId) throw new Error('Spot inválido');

  const { error } = await supabase
    .from('spots')
    .update({ status: nextStatus })
    .eq('id', spotId);

  if (error) throw new Error(error.message || 'No se pudo actualizar moderación');
  return { success: true };
}

async function handleModerationStats() {
  await ensureModeratorRole();
  const supabase = getSupabaseClient();

  const [{ count: spotsTotal, error: spotsError }, { count: reportsPending, error: reportsError }, { data: ratingsRows, error: ratingsError }] = await Promise.all([
    supabase.from('spots').select('id', { count: 'exact', head: true }),
    supabase.from('reports').select('id', { count: 'exact', head: true }).eq('status', 'pending'),
    supabase.from('ratings').select('rating'),
  ]);

  if (spotsError) throw new Error(spotsError.message || 'No se pudieron cargar métricas de spots');
  if (reportsError) throw new Error(reportsError.message || 'No se pudieron cargar métricas de reportes');
  if (ratingsError) throw new Error(ratingsError.message || 'No se pudieron cargar métricas de ratings');

  const values = (ratingsRows || []).map((row) => Number(row.rating)).filter((rating) => Number.isFinite(rating));
  const average = values.length > 0 ? values.reduce((acc, rating) => acc + rating, 0) / values.length : 0;

  return {
    data: {
      spotsTotal: Number(spotsTotal || 0),
      reportsPending: Number(reportsPending || 0),
      averageRatingGlobal: Number(average.toFixed(2)),
    },
  };
}

async function handleGetNotifications(pathSearch) {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const limit = Math.max(1, Math.min(100, toInt(pathSearch.searchParams.get('limit'), 20)));
  const unreadOnly = String(pathSearch.searchParams.get('unread_only') || '').toLowerCase() === 'true';

  let query = supabase
    .from('notifications')
    .select('*')
    .eq('user_id', session.user.id)
    .order('created_at', { ascending: false })
    .limit(limit);

  if (unreadOnly) {
    query = query.eq('is_read', false);
  }

  const { data, error } = await query;
  if (error) throw new Error(error.message || 'No se pudieron cargar notificaciones');

  return {
    data: {
      notifications: Array.isArray(data) ? data : [],
    },
  };
}

async function handleUnreadCount() {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const { count, error } = await supabase
    .from('notifications')
    .select('id', { count: 'exact', head: true })
    .eq('user_id', session.user.id)
    .eq('is_read', false);

  if (error) throw new Error(error.message || 'No se pudo cargar el contador de no leídas');
  return { data: { count: Number(count || 0) } };
}

async function handleNotificationRead(path) {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const match = path.match(/^\/notifications\/(\d+)\/read$/);
  const notificationId = Number(match?.[1] || 0);
  if (!notificationId) throw new Error('Notificación inválida');

  const { error } = await supabase
    .from('notifications')
    .update({ is_read: true })
    .eq('id', notificationId)
    .eq('user_id', session.user.id);

  if (error) throw new Error(error.message || 'No se pudo marcar la notificación como leída');
  return { success: true };
}

async function handleMarkAllRead() {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const { error } = await supabase
    .from('notifications')
    .update({ is_read: true })
    .eq('user_id', session.user.id)
    .eq('is_read', false);

  if (error) throw new Error(error.message || 'No se pudieron marcar todas como leídas');
  return { success: true };
}

async function handleDeleteNotification(path) {
  const session = await requireSession();
  const supabase = getSupabaseClient();
  const match = path.match(/^\/notifications\/(\d+)$/);
  const notificationId = Number(match?.[1] || 0);
  if (!notificationId) throw new Error('Notificación inválida');

  const { error } = await supabase
    .from('notifications')
    .delete()
    .eq('id', notificationId)
    .eq('user_id', session.user.id);

  if (error) throw new Error(error.message || 'No se pudo eliminar la notificación');
  return { success: true };
}

export async function apiFetch(endpoint, { method = 'GET', body = null } = {}) {
  const { path, searchParams } = parseEndpoint(endpoint);
  const upperMethod = String(method || 'GET').toUpperCase();
  const parsed = { path, searchParams };

  if (upperMethod === 'GET' && path === '/spots') {
    return handleGetSpots(parsed);
  }

  if (upperMethod === 'POST' && path === '/spots') {
    return handleCreateSpot(body);
  }

  if (upperMethod === 'GET' && path === '/admin/pending') {
    return handleGetPending(parsed);
  }

  if (upperMethod === 'POST' && /^\/admin\/spots\/\d+\/approve$/.test(path)) {
    return handleModerationAction(path, 'approved');
  }

  if (upperMethod === 'POST' && /^\/admin\/spots\/\d+\/reject$/.test(path)) {
    return handleModerationAction(path, 'rejected');
  }

  if (upperMethod === 'GET' && path === '/admin/stats') {
    return handleModerationStats();
  }

  if (upperMethod === 'GET' && path === '/notifications') {
    return handleGetNotifications(parsed);
  }

  if (upperMethod === 'GET' && path === '/notifications/unread-count') {
    return handleUnreadCount();
  }

  if (upperMethod === 'PATCH' && /^\/notifications\/\d+\/read$/.test(path)) {
    return handleNotificationRead(path);
  }

  if (upperMethod === 'POST' && path === '/notifications/mark-all-read') {
    return handleMarkAllRead();
  }

  if (upperMethod === 'DELETE' && /^\/notifications\/\d+$/.test(path)) {
    return handleDeleteNotification(path);
  }

  throw new Error(`Endpoint no soportado en modo Supabase: ${upperMethod} ${path}`);
}
