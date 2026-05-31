// @ts-check
const { test, expect, waitReady } = require('../fixtures');

/**
 * Modul 0 — Smoke. Pastikan server hidup sebelum test lain jalan.
 * Kalau gagal, semua test setelahnya akan otomatis di-skip secara manual
 * lewat global error.
 */
test.describe('Smoke — server reachable', () => {
    test('SMOKE-01 landing 200 OK + render hero', async ({ page, consoleErrors, failedRequests }) => {
        const resp = await page.goto('/');
        expect(resp, 'Server harus reachable. Cek Cloudflare Tunnel / Laravel.').toBeTruthy();
        expect(resp.status(), `Status code ${resp.status()} — server tidak normal.`).toBeLessThan(400);
        await waitReady(page);

        // Hero title or topbar branding visible
        const branding = page.getByText(/aishi beauty/i).first();
        await expect(branding).toBeVisible();

        // No 5xx requests
        expect(failedRequests, 'Ada request 5xx saat load landing.').toEqual([]);
        // No critical console errors
        expect(consoleErrors, 'Console error di landing.').toEqual([]);
    });
});
