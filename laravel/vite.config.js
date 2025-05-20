import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [vue(), laravel()],
  server: {
    port: 8080,
    host: 'localhost',
    hmr: {
      host: 'localhost',
      port: 8080,
    },
    headers: {
      'Content-Security-Policy': "default-src 'self'; script-src 'self' https://apis.google.com https://www.gstatic.com 'unsafe-inline'; connect-src 'self' http://localhost:8080 http://localhost:8000 https://accounts.google.com; frame-src https://accounts.google.com;",
      'Cross-Origin-Opener-Policy': 'same-origin-allow-popups',
      'Cross-Origin-Embedder-Policy': 'unsafe-none',
      
    },
  },
  define: {
    __VUE_OPTIONS_API__: true,
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false
  
  },
});