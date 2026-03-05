import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const rawBackendBasePath = process.env.VITE_DEV_BACKEND_BASE_PATH || '/https-github.com-antonio-valero-daw2personal.worktrees/Proyecto/spotMap/backend/public/index.php'
const backendBasePath = rawBackendBasePath.startsWith('/') ? rawBackendBasePath.replace(/\/+$/, '') : `/${rawBackendBasePath.replace(/\/+$/, '')}`
const backendPublicPath = backendBasePath.endsWith('/index.php')
  ? backendBasePath.slice(0, -('/index.php'.length))
  : backendBasePath

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: (path) => `${backendBasePath}/api${path.replace(/^\/api/, '')}`,
      },
      '/auth-login.php': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: () => `${backendPublicPath}/auth-login.php`,
      },
      '/oauth-init': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: () => `${backendPublicPath}/api.php?action=oauth_init`,
      },
    },
  },
  test: {
    environment: 'happy-dom',
    globals: true,
  },
})
