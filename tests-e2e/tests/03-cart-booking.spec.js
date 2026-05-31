// @ts-check
const { test, expect, waitReady, loginAsUser } = require('../fixtures');

/**
 * Modul 7 (Cart) + Modul 4 (Booking) — butuh login.
 * Akan auto-skip kalau env credential tidak di-set.
 */

test.describe('Modul Cart + Booking (perlu login)', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsUser(page);
    });

    test('TC-16 Tambah produk → modal sukses muncul (no redirect)', async ({ page }) => {
        await page.goto('/shop');
        await waitReady(page);

        // Klik kartu produk pertama untuk masuk detail
        const firstProduct = page.locator('a[href*="/shop/"]').first();
        if (!(await firstProduct.count())) {
            test.skip(true, 'Tidak ada produk di katalog shop.');
        }
        await firstProduct.click();
        await waitReady(page);

        // Klik tombol tambah keranjang
        const addBtn = page.getByRole('button', { name: /(tambah(kan)?|add).*keranjang|keranjang/i }).first();
        await expect(addBtn).toBeVisible();

        const urlBefore = page.url();
        await addBtn.click();

        // Tunggu modal sukses
        const modal = page.getByText(/berhasil ditambahkan|ditambahkan ke keranjang/i).first();
        await expect(modal).toBeVisible({ timeout: 10_000 });

        // URL tidak berubah (tetap di detail produk)
        expect(page.url(), 'Tambah keranjang tidak boleh redirect.').toBe(urlBefore);
    });

    test('TC-70 Lihat keranjang', async ({ page }) => {
        await page.goto('/cart');
        await waitReady(page);

        // Cek heading atau empty state
        const heading = page.getByRole('heading', { name: /keranjang/i }).first();
        await expect(heading.or(page.getByText(/keranjang/i).first())).toBeVisible();
    });

    test('TC-30 Booking → cek modal profil (kalau profil belum lengkap)', async ({ page }) => {
        // Klik treatment apapun lalu Buat Janji
        await page.goto('/treatment');
        await waitReady(page);

        const firstCard = page.locator('a[href*="/treatment/"]').first();
        if (!(await firstCard.count())) {
            test.skip(true, 'Tidak ada kartu treatment.');
        }
        await firstCard.click();
        await waitReady(page);

        const bookBtn = page.getByRole('link', { name: /buat janji/i }).or(page.getByRole('button', { name: /buat janji/i })).first();
        await bookBtn.click();
        await waitReady(page);

        // Bisa salah satu dari:
        // (a) Redirect ke dashboard + modal profil amber muncul (profil belum lengkap)
        // (b) Wizard booking muncul (profil lengkap)
        const profileModal = page.getByText(/lengkapi profil/i).first();
        const wizard = page.getByText(/(pilih layanan|pilih treatment|jadwal)/i).first();

        const modalVisible = await profileModal.isVisible().catch(() => false);
        const wizardVisible = await wizard.isVisible().catch(() => false);

        expect(
            modalVisible || wizardVisible,
            'Setelah klik Buat Janji harus ke wizard booking ATAU modal profil muncul.'
        ).toBeTruthy();
    });

    test('TC-100 Dashboard tab Overview', async ({ page }) => {
        await page.goto('/dashboard');
        await waitReady(page);

        // Heading dashboard atau stats card
        const dashboardHint = page.getByText(/(dashboard|janji temu|pesanan|jadwal mendatang)/i).first();
        await expect(dashboardHint).toBeVisible();
    });
});
