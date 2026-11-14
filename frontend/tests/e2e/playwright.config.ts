import { defineConfig, devices } from '@playwright/test'

/**
 * Browser (E2E) test config for Mountain Tourism frontend (Nuxt 4 + Vuetify 3).
 *
 * Prerequisite (run once, NOT part of this change):
 *   npm install -D @playwright/test
 *   npx playwright install --with-deps
 *
 * Run:  npx playwright test
 *       npx playwright test --project=chromium
 *
 * The Nuxt dev server must be running on :3000 before tests execute.
 */
export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 2 : undefined,
  reporter: process.env.CI ? 'github' : 'list',

  // Nuxt dev server binds :3000 (see frontend/nuxt.config.ts devServer.port)
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:3000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: devices['Desktop Chrome'],
    },
    {
      name: 'webkit',
      use: devices['Desktop Safari'],
    },
    {
      name: 'firefox',
      use: devices['Desktop Firefox'],
    },
  ],

  webServer: {
    // Do NOT auto-start the dev server here. The backend (Laravel :8000) must be
    // up and reachable via the /api and /sanctum proxies in nuxt.config.ts.
    command: 'npm run dev',
    cwd: './frontend',
    port: 3000,
    timeout: 120_000,
    reuseExistingServer: !process.env.CI,
  },
})
