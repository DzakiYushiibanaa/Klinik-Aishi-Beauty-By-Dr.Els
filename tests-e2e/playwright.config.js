// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright config for Aishi Beauty user-side QA.
 * Reference: ../docs/QA_TEST_GUIDE.md
 */
module.exports = defineConfig({
    testDir: './tests',
    timeout: 60_000,
    expect: { timeout: 10_000 },
    fullyParallel: false, // sequential to avoid race on shared dev server
    workers: 1,
    retries: 0,
    reporter: [
        ['list'],
        ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ],
    use: {
        baseURL: process.env.BASE_URL || 'https://dev.aishibeauty.my.id',
        headless: false, // headed per instruction
        viewport: { width: 1440, height: 900 },
        locale: 'id-ID',
        timezoneId: 'Asia/Jakarta',
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        trace: 'on-first-retry',
        actionTimeout: 15_000,
        navigationTimeout: 30_000,
    },
    projects: [
        {
            name: 'chrome-desktop',
            testIgnore: /.*\.mobile\.spec\.js$/,
            use: {
                ...devices['Desktop Chrome'],
                channel: 'chrome',
                viewport: { width: 1440, height: 900 },
            },
        },
        {
            name: 'chrome-mobile',
            testMatch: /.*\.mobile\.spec\.js$/,
            use: {
                ...devices['Pixel 7'],
                channel: 'chrome',
            },
        },
    ],
});
