// @ts-check
const { test, expect, waitReady } = require('../fixtures');

/**
 * Modul 2 — Katalog Publik (TC-10..TC-18).
 * Read-only test: tidak ada submit / tidak menyentuh DB.
 */

test.describe('Modul 2 — Katalog Publik', () => {
    test('TC-10 @happy Browse Treatment + grid responsif', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        await expect(page).toHaveTitle(/treatment|aishi/i);

        // Setidaknya 1 kartu treatment muncul di area main
        const main = page.getByRole('main');
        const card = main.locator('a[href*="/treatment/"]').first();
        await expect(card).toBeVisible({ timeout: 15_000 });
    });

    test('TC-11 Sort treatment — opsi tersedia', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        const sortSelect = page.locator('select[name="sort"], select#sort').first();
        if (await sortSelect.count()) {
            const options = await sortSelect.locator('option').allTextContents();
            const lowered = options.map((s) => s.toLowerCase());
            expect(lowered.some((o) => /termurah|terendah/.test(o)), 'Opsi sort termurah harus ada').toBeTruthy();
            expect(lowered.some((o) => /tertinggi|termahal/.test(o)), 'Opsi sort tertinggi harus ada').toBeTruthy();
        } else {
            test.info().annotations.push({ type: 'note', description: 'Sort dropdown tidak ditemukan (mungkin disembunyikan di mobile).' });
        }
    });

    test('TC-13 Search treatment', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        const search = page.locator('input[type="search"], input[name="search"], input[placeholder*="cari" i]').first();
        if (!(await search.count())) {
            test.skip(true, 'Field search tidak ditemukan.');
        }
        await search.fill('facial');
        await search.press('Enter');
        await waitReady(page);

        expect(page.url()).toMatch(/[?&]search=/);
    });

    test('TC-14 Detail Treatment punya CTA Buat Janji', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        const main = page.getByRole('main');
        const firstCard = main.locator('a[href*="/treatment/"]').first();
        if (!(await firstCard.count())) {
            test.skip(true, 'Tidak ada kartu treatment.');
        }
        await firstCard.click();
        await waitReady(page);

        // Scope ke main biar nggak nyambar footer
        const detailMain = page.getByRole('main');
        const cta = detailMain.getByRole('link', { name: /buat janji/i })
            .or(detailMain.getByRole('button', { name: /buat janji/i }))
            .first();
        await expect(cta).toBeVisible({ timeout: 10_000 });
    });

    test('TC-15 Browse Shop', async ({ page }) => {
        await page.goto('/shop');
        await waitReady(page);
        await expect(page.getByRole('main').locator('text=/tambah keranjang|tersedia|stok/i').first()).toBeVisible({ timeout: 15_000 });
    });

    test('TC-17 Tambah keranjang saat belum login → redirect login', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto('/shop');
        await waitReady(page);

        const main = page.getByRole('main');
        const productLink = main.locator('a[href*="/shop/"]').first();
        if (!(await productLink.count())) {
            test.skip(true, 'Tidak ada produk di /shop.');
        }
        await productLink.hover();

        // Bypass AJAX handler (data-cart-form) supaya kita bisa observe HTTP redirect
        await page.evaluate(() => {
            document.querySelectorAll('form[data-cart-form]').forEach((f) =>
                f.removeAttribute('data-cart-form')
            );
        });

        const addBtn = main.locator('form[action*="cart/add"] button[type="submit"]').first();
        if (!(await addBtn.count())) {
            test.skip(true, 'Form add-to-cart tidak ditemukan di list shop.');
        }

        await Promise.all([
            page.waitForURL(/\/login|\/register/, { timeout: 15_000 }),
            addBtn.click(),
        ]);

        expect(page.url()).toMatch(/\/login|\/register/);
    });

    test('TC-18 Browse Bundle', async ({ page }) => {
        const resp = await page.goto('/bundle');
        if (resp && resp.status() === 404) {
            test.skip(true, 'Route /bundle tidak tersedia.');
        }
        await waitReady(page);
        await expect(page.locator('body')).toBeVisible();
    });
});
