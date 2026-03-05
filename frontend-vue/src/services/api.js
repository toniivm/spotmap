import { getSupabaseClient, getUserRole, requireSession } from './supabase';

const FEATURES = {
  notifications: String(import.meta.env.VITE_FEATURE_NOTIFICATIONS || 'false').toLowerCase() === 'true',
  ratingsStats: String(import.meta.env.VITE_FEATURE_RATINGS_STATS || 'false').toLowerCase() === 'true',
};

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

function normalizeTags(raw) {
  if (!raw) return [];
  if (Array.isArray(raw)) return raw.map((value) => String(value).trim()).filter(Boolean);
  if (typeof raw === 'string') {
    return raw.split(',').map((value) => value.trim()).filter(Boolean);
  }
  return [];
}

function normalizeSpotRow(row = {}) {
  return {
    ...row,
    tags: normalizeTags(row.tags),
  };
}

function getErrorText(error) {
  return [error?.message, error?.details, error?.hint, error?.code]
    .filter(Boolean)
    .join(' ')
    .toLowerCase();
}

function isMissingTableError(error, tableName) {
  const text = getErrorText(error);
  if (!text) return false;
  return (
    text.includes('42p01')
    || text.includes('pgrst205')
    || text.includes(`relation "public.${tableName}" does not exist`)
    || text.includes(`relation "${tableName}" does not exist`)
    || text.includes(`could not find the table 'public.${tableName}'`)
  );
}

async function createReviewNotification(userId, spotId, spotTitle) {
  if (!FEATURES.notifications) {
    return;
  }

  const supabase = getSupabaseClient();
  const { error } = await supabase
    .from('notifications')
    .insert({
      user_id: userId,
      title: 'Spot en revision',
      message: `Tu spot "${spotTitle}" esta siendo revisado por moderacion.`,
      type: 'spot_pending',
      is_read: false,
      related_spot_id: spotId,
    });

  if (error && !isMissingTableError(error, 'notifications')) {
    throw new Error(error.message || 'No se pudo crear la notificacion de revision');
  }
}

async function requireModeratorSession() {
  const session = await requireSession();
  const role = await getUserRole(session.user.id);
  if (!['admin', 'moderator'].includes(role)) {
    throw new Error('Forbidden');
  }
  return session;
}

async function requireSpotManagementPermission(spotId) {
  const session = await requireSession();
  const role = await getUserRole(session.user.id);
  if (['admin', 'moderator'].includes(role)) {
    return { session, role, isOwner: false };
  }

  const supabase = getSupabaseClient();
  const { data: spot, error } = await supabase
    .from('spots')
    .select('id,user_id')
    .eq('id', spotId)
    .maybeSingle();

  if (error) {
    throw new Error(error.message || 'No se pudo validar permisos');
  }
  if (!spot?.id) {
    throw new Error('Spot no encontrado');
  }
  if (String(spot.user_id || '') !== String(session.user.id)) {
    throw new Error('Forbidden');
  }

  return { session, role, isOwner: true };
}

async function getSpots(searchParams) {
  const supabase = getSupabaseClient();
  const page = Math.max(1, toInt(searchParams.get('page'), 1));
  const limit = Math.max(1, Math.min(100, toInt(searchParams.get('limit'), 24)));
  const category = String(searchParams.get('category') || '').trim();
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

  const { error: uploadError } = await supabase
    .storage
    .from('spot-images')
    .upload(path, file, { upsert: false });

  if (uploadError) {
    throw new Error(uploadError.message || 'No se pudo subir la imagen');
  }

  const { data } = supabase.storage.from('spot-images').getPublicUrl(path);
  return data?.publicUrl || '';
}

async function createSpot(body) {
  const session = await requireSession();
  const role = await getUserRole(session.user.id);
  const supabase = getSupabaseClient();

  if (!(body instanceof FormData)) {
    throw new Error('Formato inválido para crear spot');
  }

  const title = String(body.get('title') || '').trim();
  const description = String(body.get('description') || '').trim();
  const category = String(body.get('category') || '').trim();
  const lat = Number(body.get('lat'));
  const lng = Number(body.get('lng'));

  if (!title) throw new Error('El título es obligatorio');
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) throw new Error('Coordenadas inválidas');

  const tagsRaw = String(body.get('tags') || '').trim();
  let tags = [];
  try {
    const parsed = tagsRaw ? JSON.parse(tagsRaw) : [];
    tags = normalizeTags(parsed);
  } catch {
    tags = normalizeTags(tagsRaw);
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
      throw new Error(updateError.message || 'No se pudieron guardar las imágenes');
    }

    if (status === 'pending') {
      await createReviewNotification(session.user.id, created.id, title);
    }
    return { data: normalizeSpotRow(updated) };
  }

  if (status === 'pending') {
    await createReviewNotification(session.user.id, created.id, title);
  }

  return { data: normalizeSpotRow(created) };
}

async function editPendingSpot(path, body) {
  await requireModeratorSession();
  const supabase = getSupabaseClient();
  const match = path.match(/^\/admin\/spots\/(\d+)\/edit$/);
  const spotId = Number(match?.[1] || 0);
  if (!spotId) throw new Error('Spot invalido');

  if (!(body instanceof FormData)) {
    throw new Error('Formato invalido para editar spot pendiente');
  }

  const { data: currentSpot, error: currentError } = await supabase
    .from('spots')
    .select('id,status')
    .eq('id', spotId)
    .maybeSingle();

  if (currentError) {
    throw new Error(currentError.message || 'No se pudo cargar el spot pendiente');
  }
  if (!currentSpot?.id) {
    throw new Error('Spot no encontrado');
  }
  if (String(currentSpot.status || '') !== 'pending') {
    throw new Error('Solo se pueden editar spots pendientes');
  }

  const updates = {};

  if (body.has('title')) {
    const title = String(body.get('title') || '').trim();
    if (!title) throw new Error('El titulo es obligatorio');
    updates.title = title;
  }

  if (body.has('description')) {
    updates.description = String(body.get('description') || '').trim();
  }

  if (body.has('category')) {
    updates.category = String(body.get('category') || '').trim();
  }

  if (body.has('tags')) {
    const tagsRaw = String(body.get('tags') || '').trim();
    try {
      updates.tags = normalizeTags(tagsRaw ? JSON.parse(tagsRaw) : []);
    } catch {
      updates.tags = normalizeTags(tagsRaw);
    }
  }

  if (body.has('lat')) {
    const lat = Number(body.get('lat'));
    if (!Number.isFinite(lat) || lat < -90 || lat > 90) throw new Error('Latitud invalida');
    updates.lat = lat;
  }

  if (body.has('lng')) {
    const lng = Number(body.get('lng'));
    if (!Number.isFinite(lng) || lng < -180 || lng > 180) throw new Error('Longitud invalida');
    updates.lng = lng;
  }

  const image1 = body.get('image1');
  const image2 = body.get('image2');
  if (image1 instanceof File) {
    updates.image_path = await uploadSpotImage(image1, spotId, 1);
  }
  if (image2 instanceof File) {
    updates.image_path_2 = await uploadSpotImage(image2, spotId, 2);
  }

  if (Object.keys(updates).length === 0) {
    throw new Error('No hay cambios para guardar');
  }

  const { data, error } = await supabase
    .from('spots')
    .update(updates)
    .eq('id', spotId)
    .select('*')
    .single();

  if (error) {
    throw new Error(error.message || 'No se pudo editar el spot pendiente');
  }

  return { data: normalizeSpotRow(data) };
}

async function updateSpot(path, body) {
  const match = path.match(/^\/spots\/(\d+)$/);
  const spotId = Number(match?.[1] || 0);
  if (!spotId) throw new Error('Spot inválido');

  await requireSpotManagementPermission(spotId);

  if (!body || typeof body !== 'object' || body instanceof FormData) {
    throw new Error('Formato inválido para actualizar spot');
  }

  const updates = {};
  if (body.title !== undefined) updates.title = String(body.title || '').trim();
  if (body.description !== undefined) updates.description = String(body.description || '').trim();
  if (body.category !== undefined) updates.category = String(body.category || '').trim();
  if (body.tags !== undefined) updates.tags = normalizeTags(body.tags);
  if (body.lat !== undefined) {
    const lat = Number(body.lat);
    if (!Number.isFinite(lat) || lat < -90 || lat > 90) throw new Error('Latitud inválida');
    updates.lat = lat;
  }
  if (body.lng !== undefined) {
    const lng = Number(body.lng);
    if (!Number.isFinite(lng) || lng < -180 || lng > 180) throw new Error('Longitud inválida');
    updates.lng = lng;
  }

  if (Object.keys(updates).length === 0) {
    throw new Error('No hay cambios para guardar');
  }

  const supabase = getSupabaseClient();
  const { data, error } = await supabase
    .from('spots')
    .update(updates)
    .eq('id', spotId)
    .select('*')
    .single();

  if (error) throw new Error(error.message || 'No se pudo actualizar el spot');
  return { data: normalizeSpotRow(data) };
}

async function deleteSpot(path) {
  const match = path.match(/^\/spots\/(\d+)$/);
  const spotId = Number(match?.[1] || 0);
  if (!spotId) throw new Error('Spot inválido');

  await requireSpotManagementPermission(spotId);

  const supabase = getSupabaseClient();
  const { error } = await supabase
    .from('spots')
    .delete()
    .eq('id', spotId);

  if (error) throw new Error(error.message || 'No se pudo eliminar el spot');
  return { success: true };
}

async function getPending(searchParams) {
  await requireModeratorSession();
  const supabase = getSupabaseClient();
  const page = Math.max(1, toInt(searchParams.get('page'), 1));
  const limit = Math.max(1, Math.min(100, toInt(searchParams.get('limit'), 50)));
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

async function updateSpotStatus(path, nextStatus) {
  await requireModeratorSession();
  const supabase = getSupabaseClient();
  const match = path.match(/^\/admin\/spots\/(\d+)\/(approve|reject)$/);
  const spotId = Number(match?.[1] || 0);
  if (!spotId) throw new Error('Spot inválido');

  const { error } = await supabase
    .from('spots')
    .update({ status: nextStatus })
    .eq('id', spotId);

  if (error) throw new Error(error.message || 'No se pudo actualizar estado');
  return { success: true };
}

async function getModerationStats() {
  await requireModeratorSession();
  const supabase = getSupabaseClient();

  const [spotsRes, reportsRes, ratingsRes] = await Promise.all([
    supabase.from('spots').select('id', { count: 'exact', head: true }),
    supabase.from('reports').select('id', { count: 'exact', head: true }).eq('status', 'pending'),
    FEATURES.ratingsStats
      ? supabase.from('ratings').select('rating')
      : Promise.resolve({ data: [], error: null }),
  ]);

  if (spotsRes.error) throw new Error(spotsRes.error.message || 'Error cargando spots');
  if (reportsRes.error && !isMissingTableError(reportsRes.error, 'reports')) {
    throw new Error(reportsRes.error.message || 'Error cargando reportes');
  }
  if (ratingsRes.error && !isMissingTableError(ratingsRes.error, 'ratings')) {
    throw new Error(ratingsRes.error.message || 'Error cargando ratings');
  }

  const ratings = (Array.isArray(ratingsRes.data) ? ratingsRes.data : [])
    .map((row) => Number(row.rating))
    .filter((value) => Number.isFinite(value));

  const average = ratings.length
    ? ratings.reduce((acc, value) => acc + value, 0) / ratings.length
    : 0;

  return {
    data: {
      spotsTotal: Number(spotsRes.count || 0),
      reportsPending: Number(reportsRes.count || 0),
      averageRatingGlobal: Number(average.toFixed(2)),
    },
  };
}

async function getNotifications(searchParams) {
  if (!FEATURES.notifications) {
    return {
      data: {
        notifications: [],
      },
    };
  }

  const session = await requireSession();
  const supabase = getSupabaseClient();
  const limit = Math.max(1, Math.min(100, toInt(searchParams.get('limit'), 20)));
  const unreadOnly = String(searchParams.get('unread_only') || '').toLowerCase() === 'true';

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
  if (error) {
    if (isMissingTableError(error, 'notifications')) {
      return {
        data: {
          notifications: [],
        },
      };
    }
    throw new Error(error.message || 'No se pudieron cargar notificaciones');
  }

  return {
    data: {
      notifications: Array.isArray(data) ? data : [],
    },
  };
}

async function getUnreadCount() {
  if (!FEATURES.notifications) {
    return { data: { count: 0 } };
  }

  const session = await requireSession();
  const supabase = getSupabaseClient();
  const { count, error } = await supabase
    .from('notifications')
    .select('id', { count: 'exact', head: true })
    .eq('user_id', session.user.id)
    .eq('is_read', false);

  if (error) {
    if (isMissingTableError(error, 'notifications')) {
      return { data: { count: 0 } };
    }
    throw new Error(error.message || 'No se pudo cargar el contador');
  }
  return { data: { count: Number(count || 0) } };
}

async function markNotificationRead(path) {
  if (!FEATURES.notifications) {
    return { success: true };
  }

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

  if (error && !isMissingTableError(error, 'notifications')) {
    throw new Error(error.message || 'No se pudo marcar como leída');
  }
  return { success: true };
}

async function markAllNotificationsRead() {
  if (!FEATURES.notifications) {
    return { success: true };
  }

  const session = await requireSession();
  const supabase = getSupabaseClient();
  const { error } = await supabase
    .from('notifications')
    .update({ is_read: true })
    .eq('user_id', session.user.id)
    .eq('is_read', false);

  if (error && !isMissingTableError(error, 'notifications')) {
    throw new Error(error.message || 'No se pudieron marcar todas como leídas');
  }
  return { success: true };
}

async function deleteNotification(path) {
  if (!FEATURES.notifications) {
    return { success: true };
  }

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

  if (error && !isMissingTableError(error, 'notifications')) {
    throw new Error(error.message || 'No se pudo eliminar notificación');
  }
  return { success: true };
}

export async function apiFetch(endpoint, { method = 'GET', body = null } = {}) {
  const { path, searchParams } = parseEndpoint(endpoint);
  const upperMethod = String(method || 'GET').toUpperCase();

  if (upperMethod === 'GET' && path === '/spots') {
    return getSpots(searchParams);
  }

  if (upperMethod === 'POST' && path === '/spots') {
    return createSpot(body);
  }

  if ((upperMethod === 'PATCH' || upperMethod === 'PUT') && /^\/spots\/\d+$/.test(path)) {
    return updateSpot(path, body);
  }

  if (upperMethod === 'DELETE' && /^\/spots\/\d+$/.test(path)) {
    return deleteSpot(path);
  }

  if (upperMethod === 'GET' && path === '/admin/pending') {
    return getPending(searchParams);
  }

  if (upperMethod === 'POST' && /^\/admin\/spots\/\d+\/approve$/.test(path)) {
    return updateSpotStatus(path, 'approved');
  }

  if (upperMethod === 'POST' && /^\/admin\/spots\/\d+\/reject$/.test(path)) {
    return updateSpotStatus(path, 'rejected');
  }

  if ((upperMethod === 'PATCH' || upperMethod === 'POST') && /^\/admin\/spots\/\d+\/edit$/.test(path)) {
    return editPendingSpot(path, body);
  }

  if (upperMethod === 'GET' && path === '/admin/stats') {
    return getModerationStats();
  }

  if (upperMethod === 'GET' && path === '/notifications') {
    return getNotifications(searchParams);
  }

  if (upperMethod === 'GET' && path === '/notifications/unread-count') {
    return getUnreadCount();
  }

  if (upperMethod === 'PATCH' && /^\/notifications\/\d+\/read$/.test(path)) {
    return markNotificationRead(path);
  }

  if (upperMethod === 'POST' && path === '/notifications/mark-all-read') {
    return markAllNotificationsRead();
  }

  if (upperMethod === 'DELETE' && /^\/notifications\/\d+$/.test(path)) {
    return deleteNotification(path);
  }

  throw new Error(`Endpoint no soportado en modo Supabase: ${upperMethod} ${path}`);
}
