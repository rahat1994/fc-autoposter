import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Configuration constants
const DEV_PORT = 5173
const DEV_HOST = '0.0.0.0' // Allow external connections
const DEV_ORIGIN = `http://localhost:${DEV_PORT}`

// Common local development domains - add your local domain here
const ALLOWED_ORIGINS = [
  'http://localhost',
  'https://localhost', 
  'http://127.0.0.1',
  'https://127.0.0.1',
  'http://testing-ground.test', // Laravel Herd domain pattern
  'https://testing-ground.test',
  'http://testing_ground.test', // Alternative naming
  'https://testing_ground.test',
  'http://fc-autoposter.test',
  'https://fc-autoposter.test',
  // WordPress multisite patterns
  'http://wp-content.test',
  'https://wp-content.test',
  // Add your specific local domain here
]

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
    host: DEV_HOST, // Allow connections from any IP
    port: DEV_PORT,
    strictPort: false, // Allow different port if 5173 is taken
    origin: DEV_ORIGIN,
    cors: {
      origin: function (origin, callback) {
        // Allow requests with no origin (mobile apps, Postman, etc.)
        if (!origin) return callback(null, true)
        
        // Check if origin starts with any allowed pattern
        const isAllowed = ALLOWED_ORIGINS.some(allowedOrigin => {
          if (origin.startsWith(allowedOrigin)) return true
          // Also allow with port variations
          const originWithoutPort = origin.replace(/:\d+$/, '')
          return allowedOrigin.startsWith(originWithoutPort)
        })
        
        if (isAllowed) {
          callback(null, true)
        } else {
          console.warn(`CORS blocked: ${origin} not in allowed origins`)
          callback(new Error('Not allowed by CORS'), false)
        }
      },
      credentials: true,
      optionsSuccessStatus: 200,
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin']
    },
    hmr: {
      port: DEV_PORT,
      host: 'localhost'
    }
  }
})
