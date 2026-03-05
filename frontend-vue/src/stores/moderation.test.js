import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useModerationStore } from './moderation';

vi.mock('../services/auth', () => ({
  getStoredAccessToken: vi.fn(() => 'token-123'),
}));

vi.mock('../services/moderation', () => ({
  fetchPendingSpots: vi.fn(),
  approvePendingSpot: vi.fn(),
  rejectPendingSpot: vi.fn(),
  isModerationUnsupported: vi.fn(() => false),
}));

import {
  fetchPendingSpots,
  approvePendingSpot,
  rejectPendingSpot,
  isModerationUnsupported,
} from '../services/moderation';

describe('useModerationStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    fetchPendingSpots.mockResolvedValue({
      spots: [{ id: 10, title: 'Pendiente', description: '', category: 'test', lat: 1, lng: 2, createdAt: '' }],
      total: 1,
    });
  });

  it('carga pendientes correctamente', async () => {
    const store = useModerationStore();
    await store.loadPending();

    expect(store.pendingSpots).toHaveLength(1);
    expect(store.totalPending).toBe(1);
  });

  it('aprueba spot y recarga', async () => {
    const store = useModerationStore();
    await store.approve(10);

    expect(approvePendingSpot).toHaveBeenCalledWith(10, { token: 'token-123' });
    expect(fetchPendingSpots).toHaveBeenCalled();
  });

  it('rechaza spot y recarga', async () => {
    const store = useModerationStore();
    await store.reject(10);

    expect(rejectPendingSpot).toHaveBeenCalledWith(10, { token: 'token-123' });
  });

  it('desactiva soporte en backend no compatible', async () => {
    isModerationUnsupported.mockReturnValueOnce(true);
    fetchPendingSpots.mockRejectedValueOnce(new Error('Moderation requires Supabase backend'));

    const store = useModerationStore();
    await store.loadPending();

    expect(store.supported).toBe(false);
    expect(store.pendingSpots).toHaveLength(0);
  });
});