import { runtimeConfig } from '../config/runtime';

function buildApiUrl(endpoint) {
  const normalized = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
  return `${runtimeConfig.apiBase}${normalized}`;
}

async function parseJsonSafe(response) {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

export async function apiFetch(endpoint, { method = 'GET', body = null, token = null } = {}) {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), runtimeConfig.timeoutMs);

  try {
    const headers = new Headers({ Accept: 'application/json' });

    if (!(body instanceof FormData) && body !== null) {
      headers.set('Content-Type', 'application/json');
    }
    if (token) {
      headers.set('Authorization', `Bearer ${token}`);
    }

    const response = await fetch(buildApiUrl(endpoint), {
      method,
      headers,
      body: body === null ? null : body instanceof FormData ? body : JSON.stringify(body),
      signal: controller.signal,
    });

    if (!response.ok) {
      const contentType = response.headers.get('content-type') || '';
      const payload = contentType.includes('application/json') ? await parseJsonSafe(response) : await response.text();
      const message = typeof payload === 'string'
        ? payload || `HTTP ${response.status}`
        : payload?.message || payload?.error || `HTTP ${response.status}`;
      throw new Error(message);
    }

    if (response.status === 204) {
      return null;
    }

    const successContentType = response.headers.get('content-type') || '';
    if (!successContentType.includes('application/json')) {
      return null;
    }

    const data = await parseJsonSafe(response);
    if (data === null) {
      throw new Error('Respuesta JSON inválida del servidor');
    }
    return data;
  } finally {
    clearTimeout(timeout);
  }
}
