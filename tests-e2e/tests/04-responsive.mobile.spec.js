// @ts-check
const { test, expect, waitReady } = require('../fixtures');

/**
 * Modul 11 — Visual & Responsivitas (TC-120..TC-124).
 * Hanya jalan di project chrome-mobile (Pixel 7).
 */

test.describe('Modul 11 — Mobile Responsiveness', () => {
    test('TC-120 Landing tidak ada horizontal scroll', async ({ page }) => {
        await page.goto('/');
        await waitReady(page);

        const hasOverflow = await page.evaluate(
            () => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1
        );
        expect(hasOverflow, 'Landing horizontal scroll di mobile.').toBeFalsy();
    });

    test('TC-120 Treatment card 2 kolom di mobile', async ({ page }) => {
        await page.goto('/treatment');
        await waitReady(page);

        // Kartu treatment dibungkus <article>; lebih stabil daripada <a>
        const main = page.getByRole('main');
        const articles = main.locator('article');
        const count = await articles.count();
        if (count < 2) {
            test.skip(true, `Treatment kurang dari 2 (count=${count}) untuk uji 2-kolom.`);
        }

        const box1 = await articles.nth(0).boundingBox();
        const box2 = await articles.nth(1).boundingBox();
        expect(box1 && box2, 'Bounding box artikel treatment harus terbaca.').toBeTruthy();

        // 2 kolom = posisi y mirip, x berbeda
        const sameRow = Math.abs((box1.y || 0) - (box2.y || 0)) < 50;
        const differentColumn = Math.abs((box1.x || 0) - (box2.x || 0)) > 50;
        expect(sameRow && differentColumn,
            `Kartu harus 2 kolom di mobile. y diff=${Math.abs(box1.y - box2.y).toFixed(1)}, x diff=${Math.abs(box1.x - box2.x).toFixed(1)}`
        ).toBeTruthy();
    });

    test('TC-120 Shop tidak horizontal scroll', async ({ page }) => {
        await page.goto('/shop');
        await waitReady(page);
        const hasOverflow = await page.evaluate(
            () => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1
        );
        expect(hasOverflow).toBeFalsy();
    });

    test('TC-122 Hero landing tampil dengan tinggi memadai', async ({ page }) => {
        await page.goto('/');
        await waitReady(page);

        // Cari section hero (paling pertama di main, bukan navbar)
        // Welcome.blade pakai <section class="..."> untuk hero
        const heroHeight = await page.evaluate(() => {
            const main = document.querySelector('main');
            const sections = main ? main.querySelectorAll('section') : document.querySelectorAll('section');
            // Ambil section paling pertama yang tinggi-nya signifikan
            for (const s of sections) {
                const rect = s.getBoundingClientRect();
                if (rect.height > 200) return rect.height;
            }
            return 0;
        });

        const viewport = page.viewportSize();
        expect(heroHeight, 'Hero/section pertama harus punya tinggi signifikan.').toBeGreaterThan(300);
        expect(heroHeight, 'Hero tidak boleh > 2x viewport.').toBeLessThan((viewport?.height || 800) * 2);
    });

    test('TC-124 Widget WhatsApp tampil di mobile', async ({ page }) => {
        await page.goto('/');
        await waitReady(page);

        // Widget WA biasanya pakai class fixed bottom-right + ikon SVG
        const waWidget = page.locator('a[href*="wa.me"], a[href*="whatsapp"], [aria-label*="WhatsApp" i]').first();
        if (!(await waWidget.count())) {
            test.skip(true, 'Widget WhatsApp tidak ditemukan dalam DOM.');
        }
        await expect(waWidget).toBeVisible();
    });
});
