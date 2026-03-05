import { runtimeConfig } from '../config/runtime';
import { getSupabaseClient, getUserRole } from './supabase';

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

async function mapSessionUser(session) {
  const user = session?.user;
  if (!user?.id) return null;

  const role = await getUserRole(user.id);
  return normalizeUser(
    {
      id: user.id,
      email: user.email || '',
      role,
      user_metadata: user.user_metadata || {},
      app_metadata: user.app_metadata || {},
    },
    user.email || '',
  );
}

export async function loadSession() {
  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.getSession();
  if (error || !data?.session) {
    clearSessionStorage();
    return null;
  }

  const mapped = await mapSessionUser(data.session);
  if (!mapped) {
    clearSessionStorage();
    return null;
  }

  persistSession(data.session.access_token || '', mapped);
  return mapped;
}

export async function signIn(email, password) {
  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.signInWithPassword({
    email: String(email || '').trim(),
    password: String(password || ''),
  });

  if (error || !data?.session) {
    throw new Error(error?.message || 'No se pudo iniciar sesión');
  }

  const mapped = await mapSessionUser(data.session);
  if (!mapped) {
    throw new Error('No se pudo resolver el usuario');
  }

  persistSession(data.session.access_token || '', mapped);
  return mapped;
}

export async function signUp(name, email, password) {
  const supabase = getSupabaseClient();
  const cleanName = String(name || '').trim();
  const cleanEmail = String(email || '').trim();

  const { data, error } = await supabase.auth.signUp({
    email: cleanEmail,
    password: String(password || ''),
    options: {
      data: {
        full_name: cleanName,
        name: cleanName,
      },
    },
  });

  if (error) {
    throw new Error(error.message || 'No se pudo completar el registro');
  }

  if (!data?.session) {
    throw new Error('Cuenta creada. Revisa tu email para confirmar y luego inicia sesión.');
  }

  const mapped = await mapSessionUser(data.session);
  if (!mapped) {
    throw new Error('No se pudo resolver el usuario');
  }

  persistSession(data.session.access_token || '', mapped);
  return mapped;
}

export async function signOut() {
  const supabase = getSupabaseClient();
  await supabase.auth.signOut();
  clearSessionStorage();
}

export async function startOAuth(provider) {
  if (!runtimeConfig.oauthEnabled) {
    throw new Error('OAuth deshabilitado');
  }

  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.signInWithOAuth({
    provider,
    options: {
      redirectTo: window.location.origin + window.location.pathname,
    },
  });

  if (error) {
    throw new Error(error.message || 'No se pudo iniciar OAuth');
  }

  if (data?.url) {
    window.location.href = data.url;
  }
}

export async function requestPasswordReset(email) {
  const supabase = getSupabaseClient();
  const cleanEmail = String(email || '').trim();
  if (!cleanEmail) {
    throw new Error('El email es obligatorio');
  }

  const { error } = await supabase.auth.resetPasswordForEmail(cleanEmail, {
    redirectTo: window.location.origin + window.location.pathname,
  });

  if (error) {
    throw new Error(error.message || 'No se pudo enviar el correo de recuperación');
  }
  return true;
}

export async function resendVerification(email) {
  const supabase = getSupabaseClient();
  const cleanEmail = String(email || '').trim();
  if (!cleanEmail) {
    throw new Error('El email es obligatorio');
  }

  const { error } = await supabase.auth.resend({
    type: 'signup',
    email: cleanEmail,
    options: {
      emailRedirectTo: window.location.origin + window.location.pathname,
    },
  });

  if (error) {
    throw new Error(error.message || 'No se pudo reenviar el correo de verificación');
  }
  return true;
}

function getRecoveryTokensFromHash() {
  const rawHash = String(window.location.hash || '').replace(/^#/, '');
  if (!rawHash) return null;
  const params = new URLSearchParams(rawHash);
  const accessToken = String(params.get('access_token') || '');
  const refreshToken = String(params.get('refresh_token') || '');
  if (!accessToken || !refreshToken) {
    return null;
  }
  return {
    accessToken,
    refreshToken,
  };
}

async function updatePasswordWithRecoveryToken(cleanPassword, accessToken) {
  const endpoint = `${runtimeConfig.supabaseUrl}/auth/v1/user`;
  const response = await fetch(endpoint, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      apikey: runtimeConfig.supabaseAnonKey,
      Authorization: `Bearer ${accessToken}`,
    },
    body: JSON.stringify({
      password: cleanPassword,
    }),
  });

  if (!response.ok) {
    let message = 'No se pudo actualizar la contrasena';
    try {
      const payload = await response.json();
      message = payload?.msg || payload?.message || payload?.error_description || payload?.error || message;
    } catch {
      // Keep generic message when response is not JSON.
    }
    throw new Error(message);
  }
}

export async function updatePassword(newPassword) {
  const supabase = getSupabaseClient();
  const cleanPassword = String(newPassword || '');
  if (!cleanPassword) {
    throw new Error('La contrasena es obligatoria');
  }
  if (cleanPassword.length < 6) {
    throw new Error('La contrasena debe tener al menos 6 caracteres');
  }

  const recoveryTokens = getRecoveryTokensFromHash();
  if (recoveryTokens) {
    await supabase.auth.setSession({
      access_token: recoveryTokens.accessToken,
      refresh_token: recoveryTokens.refreshToken,
    });
  }

  let { error } = await supabase.auth.updateUser({
    password: cleanPassword,
  });

  if (error && String(error.message || '').toLowerCase().includes('session missing') && recoveryTokens) {
    await updatePasswordWithRecoveryToken(cleanPassword, recoveryTokens.accessToken);
    error = null;
  }

  if (error) {
    throw new Error(error.message || 'No se pudo actualizar la contrasena');
  }
  return true;
}
