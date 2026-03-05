import { describe, expect, it, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from './auth';

vi.mock('../services/auth', () => ({
  loadSession: vi.fn(),
  signIn: vi.fn(),
  signOut: vi.fn(),
  signUp: vi.fn(),
  startOAuth: vi.fn(),
  getConfiguredOAuthProviders: vi.fn(() => ['google', 'facebook']),
}));

import { loadSession, signIn, signOut, signUp, startOAuth } from '../services/auth';

const MOCK_USER = { id: '1', email: 'test@test.com', role: 'user', username: 'test' };
const MOCK_ADMIN = { id: '2', email: 'admin@test.com', role: 'admin', username: 'admin' };

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  it('estado inicial no autenticado', () => {
    const store = useAuthStore();
    expect(store.isAuthenticated).toBe(false);
    expect(store.user).toBeNull();
    expect(store.loading).toBe(false);
  });

  describe('init', () => {
    it('carga sesión activa', async () => {
      loadSession.mockResolvedValueOnce(MOCK_USER);
      const store = useAuthStore();
      await store.init();
      expect(store.isAuthenticated).toBe(true);
      expect(store.username).toBe('test');
    });

    it('queda no autenticado si no hay sesión', async () => {
      loadSession.mockResolvedValueOnce(null);
      const store = useAuthStore();
      await store.init();
      expect(store.isAuthenticated).toBe(false);
    });
  });

  describe('login', () => {
    it('autentica al usuario correctamente', async () => {
      signIn.mockResolvedValueOnce(MOCK_USER);
      const store = useAuthStore();
      await store.login('test@test.com', 'password');
      expect(store.isAuthenticated).toBe(true);
      expect(store.error).toBe('');
    });

    it('guarda error si falla', async () => {
      signIn.mockRejectedValueOnce(new Error('Credenciales inválidas'));
      const store = useAuthStore();
      await expect(store.login('bad@test.com', 'wrong')).rejects.toThrow();
      expect(store.isAuthenticated).toBe(false);
      expect(store.error).toBe('Credenciales inválidas');
    });
  });

  describe('logout', () => {
    it('limpia el usuario al salir', async () => {
      loadSession.mockResolvedValueOnce(MOCK_USER);
      signOut.mockResolvedValueOnce(undefined);
      const store = useAuthStore();
      await store.init();
      await store.logout();
      expect(store.isAuthenticated).toBe(false);
      expect(store.user).toBeNull();
    });
  });

  describe('register', () => {
    it('registra y autentica usuario correctamente', async () => {
      const newUser = { id: '3', email: 'new@test.com', role: 'user', username: 'nuevo' };
      signUp.mockResolvedValueOnce(newUser);
      const store = useAuthStore();

      await store.register('Nuevo', 'new@test.com', '123456');

      expect(store.isAuthenticated).toBe(true);
      expect(store.user?.email).toBe('new@test.com');
    });

    it('guarda error si falla el registro', async () => {
      signUp.mockRejectedValueOnce(new Error('Email ya registrado'));
      const store = useAuthStore();

      await expect(store.register('Nuevo', 'new@test.com', '123456')).rejects.toThrow();
      expect(store.error).toBe('Email ya registrado');
    });
  });

  describe('roles', () => {
    it('isAdmin es true para admin', async () => {
      signIn.mockResolvedValueOnce(MOCK_ADMIN);
      const store = useAuthStore();
      await store.login('admin@test.com', 'pass');
      expect(store.isAdmin).toBe(true);
      expect(store.isModerator).toBe(true);
    });

    it('isAdmin es false para usuario normal', async () => {
      signIn.mockResolvedValueOnce(MOCK_USER);
      const store = useAuthStore();
      await store.login('test@test.com', 'pass');
      expect(store.isAdmin).toBe(false);
    });
  });

  describe('oauth', () => {
    it('inicia oauth con proveedor configurado', async () => {
      startOAuth.mockResolvedValueOnce(undefined);
      const store = useAuthStore();

      await store.loginWithOAuth('google');

      expect(startOAuth).toHaveBeenCalledWith('google');
      expect(store.oauthError).toBe('');
      expect(store.oauthProviders).toEqual(['google', 'facebook']);
    });
  });
});
