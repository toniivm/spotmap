import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import {
  getConfiguredOAuthProviders,
  loadSession,
  requestPasswordReset,
  resendVerification,
  signIn,
  signOut,
  signUp,
  startOAuth,
  updatePassword,
} from '../services/auth';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const loading = ref(false);
  const error = ref('');
  const oauthLoading = ref(false);
  const oauthError = ref('');

  const isAuthenticated = computed(() => !!user.value?.id);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isModerator = computed(() => user.value?.role === 'moderator' || isAdmin.value);
  const username = computed(() => user.value?.username ?? user.value?.email ?? '');
  const roleLabel = computed(() => {
    const role = String(user.value?.role ?? 'user');
    if (role === 'admin') return 'Admin';
    if (role === 'moderator') return 'Moderador';
    return 'Usuario';
  });
  const oauthProviders = computed(() => getConfiguredOAuthProviders());

  async function init() {
    loading.value = true;
    error.value = '';
    try {
      user.value = await loadSession();
    } catch {
      user.value = null;
    } finally {
      loading.value = false;
    }
  }

  async function login(email, password) {
    loading.value = true;
    error.value = '';
    try {
      user.value = await signIn(email, password);
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Error al iniciar sesión';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function register(name, email, password) {
    loading.value = true;
    error.value = '';
    try {
      user.value = await signUp(name, email, password);
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Error al registrarse';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    loading.value = true;
    error.value = '';
    try {
      await signOut();
    } finally {
      user.value = null;
      loading.value = false;
    }
  }

  async function loginWithOAuth(provider) {
    oauthLoading.value = true;
    oauthError.value = '';
    try {
      await startOAuth(provider);
    } catch (err) {
      oauthError.value = err instanceof Error ? err.message : 'No se pudo iniciar OAuth';
      throw err;
    } finally {
      oauthLoading.value = false;
    }
  }

  async function sendPasswordReset(email) {
    loading.value = true;
    error.value = '';
    try {
      await requestPasswordReset(email);
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'No se pudo enviar recuperación';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function sendVerificationEmail(email) {
    loading.value = true;
    error.value = '';
    try {
      await resendVerification(email);
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'No se pudo reenviar verificación';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updatePasswordFromRecovery(newPassword) {
    loading.value = true;
    error.value = '';
    try {
      await updatePassword(newPassword);
      user.value = await loadSession();
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'No se pudo actualizar la contrasena';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  return {
    user,
    loading,
    error,
    oauthLoading,
    oauthError,
    isAuthenticated,
    isAdmin,
    isModerator,
    username,
    roleLabel,
    oauthProviders,
    init,
    login,
    register,
    sendPasswordReset,
    sendVerificationEmail,
    updatePasswordFromRecovery,
    loginWithOAuth,
    logout,
  };
});
