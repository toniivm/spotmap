import { setActivePinia, createPinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useSpotsStore } from './spots';

vi.mock('../services/api', () => ({
  apiFetch: vi.fn(),
}));

import { apiFetch } from '../services/api';

const MOCK_SPOTS = [
  { id: 1, title: 'Spot A', category: 'playa', description: 'Desc A', lat: 43.0, lng: -8.0, tags: ['tag1', 'tag2'] },
  { id: 2, title: 'Spot B', category: 'monte', description: 'Desc B', lat: 42.5, lng: -7.5, tags: 'tag2,tag3' },
  { id: 3, title: 'Spot C', category: 'playa', description: 'Desc C', lat: 43.1, lng: -8.1, tags: [] },
];

function makeApiResponse(spots, pagination = {}) {
  return {
    data: {
      spots,
      pagination: { total: spots.length, pages: 1, page: 1, ...pagination },
    },
  };
}

describe('useSpotsStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  describe('loadSpots', () => {
    it('carga y normaliza spots correctamente', async () => {
      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS));
      const store = useSpotsStore();
      await store.loadSpots();

      expect(store.spots).toHaveLength(3);
      expect(store.spots[0].title).toBe('Spot A');
      expect(store.spots[0].lat).toBe(43.0);
      expect(store.spots[1].tags).toEqual(['tag2', 'tag3']); // string → array
      expect(store.loading).toBe(false);
      expect(store.error).toBe('');
    });

    it('filtra spots sin coordenadas válidas', async () => {
      const spotsWithBad = [
        ...MOCK_SPOTS,
        { id: 99, title: 'Sin coords', category: 'otro', lat: null, lng: null, tags: [] },
      ];
      apiFetch.mockResolvedValueOnce(makeApiResponse(spotsWithBad));
      const store = useSpotsStore();
      await store.loadSpots();

      expect(store.spots).toHaveLength(3);
    });

    it('maneja error de API correctamente', async () => {
      apiFetch.mockRejectedValueOnce(new Error('Network error'));
      const store = useSpotsStore();
      await store.loadSpots();

      expect(store.spots).toHaveLength(0);
      expect(store.error).toBe('Network error');
      expect(store.loading).toBe(false);
    });

    it('actualiza paginación desde la respuesta', async () => {
      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS, { total: 50, pages: 3, page: 2 }));
      const store = useSpotsStore();
      await store.loadSpots();

      expect(store.total).toBe(50);
      expect(store.pages).toBe(3);
      expect(store.page).toBe(2);
    });
  });

  describe('filteredSpots', () => {
    beforeEach(async () => {
      apiFetch.mockResolvedValue(makeApiResponse(MOCK_SPOTS));
    });

    it('devuelve todos los spots sin filtros activos', async () => {
      const store = useSpotsStore();
      await store.loadSpots();
      expect(store.filteredSpots).toHaveLength(3);
    });

    it('filtra por búsqueda de texto', async () => {
      const store = useSpotsStore();
      await store.loadSpots();
      store.setSearchQuery('Spot A');
      expect(store.filteredSpots).toHaveLength(1);
      expect(store.filteredSpots[0].id).toBe(1);
    });

    it('filtra por categoría', async () => {
      const store = useSpotsStore();
      await store.loadSpots();
      store.categoryFilter = 'playa';
      expect(store.filteredSpots).toHaveLength(2);
    });

    it('filtra por tag', async () => {
      const store = useSpotsStore();
      await store.loadSpots();
      store.tagFilter = 'tag2';
      expect(store.filteredSpots).toHaveLength(2);
    });
  });

  describe('availableCategories y availableTags', () => {
    it('extrae categorías únicas ordenadas', async () => {
      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS));
      const store = useSpotsStore();
      await store.loadSpots();
      expect(store.availableCategories).toEqual(['monte', 'playa']);
    });

    it('extrae tags únicos', async () => {
      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS));
      const store = useSpotsStore();
      await store.loadSpots();
      expect(store.availableTags).toEqual(['tag1', 'tag2', 'tag3']);
    });
  });

  describe('paginación', () => {
    it('nextPage incrementa página y recarga', async () => {
      apiFetch.mockResolvedValue(makeApiResponse(MOCK_SPOTS, { total: 50, pages: 3, page: 1 }));
      const store = useSpotsStore();
      await store.loadSpots();

      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS, { total: 50, pages: 3, page: 2 }));
      await store.nextPage();

      expect(store.page).toBe(2);
    });

    it('prevPage no va por debajo de página 1', async () => {
      apiFetch.mockResolvedValueOnce(makeApiResponse(MOCK_SPOTS));
      const store = useSpotsStore();
      await store.loadSpots();
      await store.prevPage(); // hasPrev = false, no debe hacer nada

      expect(store.page).toBe(1);
      expect(apiFetch).toHaveBeenCalledTimes(1);
    });
  });
});
