// @ts-check
import { defineConfig, devices } from '@playwright/test'
import { AUTH_FILE, BASE_URL, E2E_DIR, SANDBOX_DIR } from './tests/e2e/support/paths.js'

/**
 * E2E suite of the Filament SDK, run against
 * `../../Projects/tagixo-filament-sandbox` — a Laravel app with Filament, the
 * core, every builder and this package — booted
 * with APP_ENV=e2e so its own database is left alone.
 *
 *   npm run test:e2e
 *   npm run test:e2e -- --headed tests/e2e/specs/panel.spec.js
 *
 * What is asserted here is what no PHP test can: that the panel renders, that the
 * editor opens from a resource, and that saving goes through the type.
 */
export default defineConfig({
    testDir: 'tests/e2e/specs',
    globalSetup: `${E2E_DIR}/global-setup.js`,

    retries: 0,
    workers: 1,
    fullyParallel: false,

    timeout: 60_000,
    expect: { timeout: 10_000 },

    reporter: [['list'], ['html', { outputFolder: `${E2E_DIR}/.report`, open: 'never' }]],
    outputDir: `${E2E_DIR}/.results`,

    use: {
        baseURL: BASE_URL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },

    projects: [
        { name: 'setup', testDir: `${E2E_DIR}/setup`, testMatch: /.*\.setup\.js/ },
        { name: 'chromium', use: { ...devices['Desktop Chrome'], storageState: AUTH_FILE }, dependencies: ['setup'] },
    ],

    // A dedicated port, and never an existing server: the suite has to know the
    // app it talks to runs with APP_ENV=e2e.
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${new URL(BASE_URL).port}`,
        cwd: SANDBOX_DIR,
        url: BASE_URL,
        reuseExistingServer: false,
        env: { APP_ENV: 'e2e' },
        stdout: 'ignore',
        stderr: 'pipe',
    },
})
