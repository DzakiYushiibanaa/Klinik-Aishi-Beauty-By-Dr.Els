// @ts-check
const { test, expect, waitReady } = require('../fixtures');

/**
 * Modul 12 — Edge Case (TC-130..TC-135).
 */

test.describe('Modul 12 — Edge Case', () => {
    test('TC-130 Akses /dashboard tanpa login → redirect /login', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto('/dashboard');
        await waitReady(page);
        expect(page.url(), 'Harus redirect ke /login.').toMatch(/\/login/);
    });

    test('TC-130 Akses /cart tanpa login → redirect /login', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto('/cart');
        await waitReady(page);
        expect(page.url()).toMatch(/\/login/);
    });

    test('TC-130 Akses /checkout tanpa login → redirect', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto('/checkout');
        await waitReady(page);
        expect(page.url()).toMatch(/\/login|\/cart/);
    });

    test('TC-132 XSS escape di search', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        const search = page.locator('input[type="search"], input[name="search"], input[placeholder*="cari" i]').first();
        if (!(await search.count())) {
            test.skip(true, 'Search field tidak tersedia.');
        }

        let alertFired = false;
        page.on('dialog', async (dialog) => {
            alertFired = true;
            await dialog.dismiss();
        });

        await search.fill('<script>alert(1)</script>');
        await search.press('Enter');
        await waitReady(page);

        expect(alertFired, 'XSS payload tidak boleh menjalankan alert.').toBeFalsy();
    });

    test('TC-135 Bahasa Indonesia di error login', async ({ page }) => {
        await page.goto('/login');
        await waitReady(page);

        await page.getByLabel(/email/i).first().fill('not.exist@aishi.test');
        await page.getByLabel(/(kata sandi|password)/i).first().fill('wrong-password');
        await page.getByRole('button', { name: /(masuk|sign in|log in)/i }).click();
        await waitReady(page);

        // Pesan error harus ID
        const body = await page.textContent('body');
        expect(body).toMatch(/(salah|tidak cocok|tidak terdaftar|tidak valid)/i);
        // Pastikan tidak bocor message default Laravel "These credentials"
        expect(body, 'Pesan default English bocor.').not.toContain('These credentials do not match');
    });
});
