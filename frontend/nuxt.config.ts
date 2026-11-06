import { fileURLToPath } from 'node:url'
import vuetify from 'vite-plugin-vuetify'
import svgLoader from 'vite-svg-loader'
import i18nConfig from './i18n.config'

export default defineNuxtConfig({
  ssr: false,
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL,
      baseUrl: process.env.BACKEND_URL,
      env: process.env.ENV,
      googleMapsApiKey: process.env.GOOGLE_MAPS_API_KEY,
    },
  },
  app: {
    head: {
      titleTemplate: '%s - Mountain Tourism',
      title: 'Mountain Tourism',
    },
  },
  nitro: {
    output: {
      publicDir: '.output/',
    },
  },
  // Bind all interfaces so lab.localhost:3000 works.
  // strictPort + HMR clientPort keep :3000 on hard refresh (Apache owns :80 here).
  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },
  devtools: {
    enabled: true,
  },
  css: [
    '@styles/css/tailwind.css',
    '@core/scss/template/index.scss',
    '@styles/styles.scss',
    '@/plugins/iconify/icons.css',
  ],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  components: {
    dirs: [{
      path: '@/@core/components',
      pathPrefix: false,
    }, {
      path: '~/components/global',
      global: true,
    }, {
      path: '~/components',
      pathPrefix: false,
    }],
  },
  plugins: ['@/plugins/vuetify/index.ts', '@/plugins/iconify/index.ts'],
  imports: {
    dirs: [
      './@core/utils',
      './@core/composable/',
      './plugins/*/composables/*',
      'types',
      './composables',
      './composables/**',
      './utils/**',
    ],
  },
  experimental: {
    typedPages: true,
  },
  typescript: {
    tsConfig: {
      compilerOptions: {
        paths: {
          '@/*': ['../*'],
          '@themeConfig': ['../themeConfig.ts'],
          '@layouts/*': ['../@layouts/*'],
          '@layouts': ['../@layouts'],
          '@core/*': ['../@core/*'],
          '@core': ['../@core'],
          '@images/*': ['../assets/images/*'],
          '@styles/*': ['../assets/styles/*'],
          '@validators': ['../@core/utils/validators'],
          '@db/*': ['../server/fake-db/*'],
          '@api-utils/*': ['../server/utils/*'],
        },
      },
    },
  },

  vite: {
    define: { 'process.env': {} },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('.', import.meta.url)),
        '@themeConfig': fileURLToPath(new URL('./themeConfig.ts', import.meta.url)),
        '@core': fileURLToPath(new URL('./@core', import.meta.url)),
        '@layouts': fileURLToPath(new URL('./@layouts', import.meta.url)),
        '@images': fileURLToPath(new URL('./assets/images/', import.meta.url)),
        '@styles': fileURLToPath(new URL('./assets/styles/', import.meta.url)),
        '@configured-variables': fileURLToPath(new URL('./assets/styles/variables/_template.scss', import.meta.url)),
        '@db': fileURLToPath(new URL('./server/fake-db/', import.meta.url)),
        '@api-utils': fileURLToPath(new URL('./server/utils/', import.meta.url)),
      },
    },
    build: {
      chunkSizeWarningLimit: 5000,
    },
    optimizeDeps: {
      exclude: ['vuetify'],
      entries: [
        './**/*.vue',
      ],
    },
    plugins: [
      svgLoader(),
      vuetify({
        styles: {
          configFile: 'assets/styles/variables/_vuetify.scss',
        },
      }),
    ],
    server: {
      host: '0.0.0.0',
      port: 3000,
      strictPort: true,
      hmr: {
        // Preserve :3000 in the browser when the page Host is lab.localhost
        clientPort: 3000,
      },
      proxy: {
        '/api': {
          // Must match frontend/.env.example BACKEND_URL (Laravel artisan serve / docker 8000|8010)
          target: process.env.BACKEND_URL || 'http://lab.localhost:8000',
          changeOrigin: true,
          secure: false,
          // Dead Laravel used to hang the browser for minutes; fail in ~8s.
          timeout: 8_000,
          proxyTimeout: 8_000,
        },
        '/sanctum': {
          target: process.env.BACKEND_URL || 'http://lab.localhost:8000',
          changeOrigin: true,
          secure: false,
          timeout: 8_000,
          proxyTimeout: 8_000,
        },
      },
    },
  },
  build: {
    transpile: ['vuetify'],
  },
  i18n: i18nConfig as any,
  modules: ['@vueuse/nuxt', '@nuxtjs/i18n', '@nuxtjs/device', '@pinia/nuxt'],
})