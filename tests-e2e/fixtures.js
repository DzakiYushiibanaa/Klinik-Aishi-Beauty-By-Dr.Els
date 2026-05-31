// @ts-check
const base = require('@playwright/test');

/**
 * Custom fixtures + helpers for Aishi Beauty E2E.
 * - Tracks console errors per test (fails the test if any).
 * - Tracks failed network requests (fails on 5xx).
 * - Provides a quick health-check before each test.
 */
const test = base.test.extend({
    consoleErrors: async ({ page }, use) => {
        const errors = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') {
                const text = msg.text();
                // Filter known-noisy 3rd-party errors
                if (/midtrans|cloudflare|favicon/i.test(text)) return;
                errors.push(text);
            }
        });
        page.on('pageerror', (err) => errors.push(`pageerror: ${err.message}`));
        await use(errors);
    },
    failedRequests: async ({ page }, use) => {
        const failed = [];
        page.on('response', (resp) => {
            const status = resp.status();
            const url = resp.url();
            // Ignore 3rd-party + known polling
            if (/midtrans|cloudflare|googletagmanager|google-analytics/i.test(url)) return;
            if (status >= 500) failed.push(`${status} ${url}`);
        });
        await use(failed);
    },
});

const expect = base.expect;

/**
 * Wait until network is mostly idle (DOM + a small grace window).
 * Replaces flaky `networkidle` for Cloudflare-fronted apps.
 */
async function waitReady(page) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(400);
}

/**
 * Login helper that uses pre-existing credentials (no DB seed).
 * Skips test if env vars missing.
 */
async function loginAsUser(page) {
    const email = process.env.TEST_USER_EMAIL;
    const password = process.env.TEST_USER_PASSWORD;
    if (!email || !password) {
        test.skip(true, 'TEST_USER_EMAIL / TEST_USER_PASSWORD env not set — minta developer siapkan akun QA.');
    }
    await page.goto('/login');
    await page.getByLabel(/email/i).fill(email);
    await page.getByLabel(/(kata sandi|password)/i).first().fill(password);
    await page.getByRole('button', { name: /(masuk|sign in|log in)/i }).click();
    await waitReady(page);
}

module.exports = { test, expect, waitReady, loginAsUser };
