# frontend-vue (SpotMap Migration)

Base de migración incremental a Vue 3 + Pinia + Leaflet.

## Requisitos
- Node 20.x (probado en 20.17)
- npm 10+

## Comandos
```powershell
cd frontend-vue
npm install
npm run dev
npm run build
```

## Backend en desarrollo (Vite)
- En local, el frontend usa `/api` y Vite hace proxy a XAMPP.
- Ruta por defecto del proxy:
	- `/https-github.com-antonio-valero-daw2personal.worktrees/Proyecto/spotMap/backend/public/index.php`
- Si tu proyecto está en otra ruta, define en `frontend-vue/.env.local`:
```bash
VITE_DEV_BACKEND_BASE_PATH=/tu/ruta/en/xampp/backend/public/index.php
```
- También puedes forzar API completa con:
```bash
VITE_API_BASE=http://localhost/tu-ruta/backend/public/index.php
```

## Estado actual
- Layout responsive (sidebar + mapa)
- Carga spots desde backend (`backend/public/index.php/spots`)
- Selección de spot en lista centra el mapa

## Nota de estrategia
Este frontend convive temporalmente con `frontend/` (legado). No sustituye producción todavía.
