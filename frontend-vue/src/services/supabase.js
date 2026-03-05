import { createClient } from '@supabase/supabase-js';
import { runtimeConfig } from '../config/runtime';

let client = null;

export function getSupabaseClient() {
  if (client) return client;

  if (!runtimeConfig.supabaseUrl || !runtimeConfig.supabaseAnonKey) {
    throw new Error('Falta configurar VITE_SUPABASE_URL y VITE_SUPABASE_ANON_KEY');
  }

  client = createClient(runtimeConfig.supabaseUrl, runtimeConfig.supabaseAnonKey, {
    auth: {
      persistSession: true,
      autoRefreshToken: true,
      detectSessionInUrl: true,
    },
  });

  return client;
}

export async function getActiveSession() {
  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.getSession();
  if (error) throw error;
  return data.session || null;
}

export async function getCurrentAuthUser() {
  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.getUser();
  if (error) throw error;
  return data.user || null;
}

export async function getUserRole(userId) {
  if (!userId) return 'user';

  const supabase = getSupabaseClient();
  const { data, error } = await supabase
    .from('profiles')
    .select('role')
    .eq('user_id', userId)
    .maybeSingle();

  if (error) {
    return 'user';
  }

  const role = String(data?.role || 'user').toLowerCase();
  return role || 'user';
}

export async function requireSession() {
  const session = await getActiveSession();
  if (!session?.user?.id) {
    throw new Error('Debes iniciar sesión');
  }
  return session;
}
