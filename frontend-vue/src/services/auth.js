import { runtimeConfig } from '../config/runtime';

const ACCESS_TOKEN_KEY = 'spotmap_access_token';
const USER_KEY = 'spotmap_user';

function normalizeUser(payload = {}, fallbackEmail = '') {
  if (!payload || typeof payload !== 'object') {
    return null;
  }

  const id = payload.id || payload.user_id || payload.sub || '';
  if (!id) return null;

  const role = payload.role || payload.user_metadata?.role || payload.app_metadata?.role || 'user';
  const email = payload.email || fallbackEmail || '';
  const username =
    payload.username
    || payload.user_metadata?.full_name
    || payload.user_metadata?.name
    || (email.includes('@') ? email.split('@')[0] : 'Usuario');

  return {
    id: String(id),
    email,
    username,
    role: String(role).toLowerCase(),
    user_metadata: payload.user_metadata || {},
    app_metadata: payload.app_metadata || {},
  };
}

export function getConfiguredOAuthProviders() {
  if (!runtimeConfig.oauthEnabled) return [];
  return Array.isArray(runtimeConfig.oauthProviders) ? runtimeConfig.oauthProviders : [];
}

export function getStoredAccessToken() {
  return (
    localStorage.getItem(ACCESS_TOKEN_KEY)
    || localStorage.getItem('spotmap_local_token')
    || localStorage.getItem('auth_token')
    || ''
  );
}

function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY) || localStorage.getItem('spotmap_local_user');
  if (!raw) return null;
  try {
    const parsed = JSON.parse(raw);
    return normalizeUser(parsed);
  } catch {
    return null;
  }
}

function persistSession(token, user) {
  if (token) {
    localStorage.setItem(ACCESS_TOKEN_KEY, token);
  }
  if (user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  }
}

function clearSessionStorage() {
  localStorage.removeItem(ACCESS_TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  localStorage.removeItem('spotmap_local_token');
  localStorage.removeItem('spotmap_local_user');
  localStorage.removeItem('auth_token');
}

export async function loadSession() {
  const token = getStoredAccessToken();
  const user = getStoredUser();
  if (!token || !user) {
    return null;
  }
  return user;
}

export async function signIn(email, password) {
  const response = await fetch(runtimeConfig.authLoginUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, password }),
  });

  const payload = await response.json().catch(() => null);
  if (!response.ok || !payload?.success) {
    throw new Error(payload?.error || payload?.message || 'No se pudo iniciar sesión');
  }

  const token = payload?.session?.access_token || '';
  const user = normalizeUser(payload?.user, email);

  if (!token || !user) {
    throw new Error('Respuesta de autenticación inválida');
  }

  persistSession(token, user);
  return user;
}

export async function signUp(name, email, password) {
  if (!runtimeConfig.backendAuthEnabled) {
    throw new Error('Registro no disponible en este entorno');
  }

  const response = await fetch(`${runtimeConfig.backendPublicBase}/api.php?action=register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ name, email, password }),
  });

  const payload = await response.json().catch(() => null);
  if (!response.ok || !payload?.success) {
    throw new Error(payload?.error || payload?.message || 'No se pudo completar el registro');
  }

  const token = payload?.session?.access_token || payload?.token || '';
  const user = normalizeUser(payload?.user, email);

  if (!token || !user) {
    throw new Error('Respuesta de registro inválida');
  }

  persistSession(token, user);
  return user;
}

export async function signOut() {
  clearSessionStorage();
}

export async function startOAuth(provider) {
  if (!runtimeConfig.oauthEnabled) {
    throw new Error('OAuth deshabilitado');
  }
  const url = `${runtimeConfig.oauthInitUrl}&provider=${encodeURIComponent(provider)}`;
  window.location.href = url;
}
