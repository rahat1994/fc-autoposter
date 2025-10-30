import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Configuration constants
const DEV_PORT = 5173
const DEV_HOST = 'localhost'
const DEV_ORIGIN = `http://${DEV_HOST}:${DEV_PORT}`

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    manifest: 'manifest.json',
    outDir: 'dist',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/main.js')
      },
      output: {
        entryFileNames: 'assets/[name].[hash].js',
        chunkFileNames: 'assets/[name].[hash].js',
        assetFileNames: 'assets/[name].[hash].[ext]'
      }
    }
  },
  server: {
    host: DEV_HOST,
    port: DEV_PORT,
    strictPort: true,
    origin: DEV_ORIGIN
  }
})
