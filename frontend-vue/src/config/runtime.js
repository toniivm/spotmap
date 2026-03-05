export function detectProjectBase(pathname = window.location.pathname) {
  const frontendIdx = pathname.lastIndexOf('/frontend-vue/');
  if (frontendIdx !== -1) {
    return pathname.substring(0, frontendIdx + 1);
  }

  const spotMapSeg = '/spotMap/';
  const spotIdx = pathname.indexOf(spotMapSeg);
  if (spotIdx !== -1) {
    return pathname.substring(0, spotIdx + spotMapSeg.length);
  }

  const lastSlash = pathname.lastIndexOf('/');
  return lastSlash > 0 ? pathname.substring(0, lastSlash + 1) : '/';
}

const projectBase = detectProjectBase().replace(/\/+$/, '/');
const envOauthEnabled = String(import.meta.env.VITE_OAUTH_ENABLED || '').trim().toLowerCase();
const envOauthProviders = String(import.meta.env.VITE_OAUTH_PROVIDERS || 'google,facebook').trim();
const supabaseUrl = String(import.meta.env.VITE_SUPABASE_URL || '').trim();
const supabaseAnonKey = String(import.meta.env.VITE_SUPABASE_ANON_KEY || '').trim();

const oauthProviders = envOauthProviders
  .split(',')
  .map((value) => value.trim().toLowerCase())
  .filter(Boolean);

export const runtimeConfig = {
  projectBase,
  supabaseUrl,
  supabaseAnonKey,
  apiBase: '/supabase',
  authLoginUrl: '',
  backendAuthEnabled: false,
  oauthEnabled: envOauthEnabled === '1' || envOauthEnabled === 'true',
  oauthProviders,
  oauthInitUrl: '',
  timeoutMs: 10000,
};
