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
const envApiBase = String(import.meta.env.VITE_API_BASE || '').trim();
const envBackendPublicBase = String(import.meta.env.VITE_BACKEND_PUBLIC_BASE || '').trim();
const envBackendAuthEnabled = String(import.meta.env.VITE_BACKEND_AUTH_ENABLED || '').trim().toLowerCase();
const envOauthEnabled = String(import.meta.env.VITE_OAUTH_ENABLED || '').trim().toLowerCase();
const envOauthProviders = String(import.meta.env.VITE_OAUTH_PROVIDERS || 'google,facebook').trim();

const backendPublicBase = envBackendPublicBase || `${window.location.origin}${projectBase}Proyecto/backend/public`;
const oauthProviders = envOauthProviders
  .split(',')
  .map((value) => value.trim().toLowerCase())
  .filter(Boolean);

export const runtimeConfig = {
  projectBase,
  backendPublicBase,
  apiBase:
    envApiBase ||
    (import.meta.env.DEV
      ? '/api'
      : `${window.location.origin}${projectBase}Proyecto/backend/public/index.php`),
  authLoginUrl: import.meta.env.DEV ? '/auth-login.php' : `${backendPublicBase}/auth-login.php`,
  backendAuthEnabled: envBackendAuthEnabled === '1' || envBackendAuthEnabled === 'true',
  oauthEnabled: envOauthEnabled === '1' || envOauthEnabled === 'true',
  oauthProviders,
  oauthInitUrl: import.meta.env.DEV ? '/oauth-init' : `${backendPublicBase}/api.php?action=oauth_init`,
  timeoutMs: 10000,
};
