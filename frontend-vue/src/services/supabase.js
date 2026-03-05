import { createClient } from '@supabase/supabase-js';
import { runtimeConfig } from '../config/runtime';

let supabaseClient = null;

export function getSupabaseClient() {
  if (supabaseClient) return supabaseClient;

  if (!runtimeConfig.supabaseUrl || !runtimeConfig.supabaseAnonKey) {
    throw new Error('Faltan VITE_SUPABASE_URL y VITE_SUPABASE_ANON_KEY');
  }

  supabaseClient = createClient(runtimeConfig.supabaseUrl, runtimeConfig.supabaseAnonKey, {
    auth: {
      persistSession: true,
      autoRefreshToken: true,
      detectSessionInUrl: true,
    },
  });

  return supabaseClient;
}

export async function requireSession() {
  const supabase = getSupabaseClient();
  const { data, error } = await supabase.auth.getSession();
  if (error) throw error;
  if (!data?.session?.user?.id) {
    throw new Error('Debes iniciar sesión');
  }
  return data.session;
}

export async function getUserRole(userId) {
  if (!userId) return 'user';

  const supabase = getSupabaseClient();
  const { data, error } = await supabase
    .from('profiles')
    .select('role')
    .eq('user_id', userId)
    .maybeSingle();

  if (error) return 'user';
  return String(data?.role || 'user').toLowerCase();
}
