// @ts-check
const { test, expect, waitReady } = require('../fixtures');

/**
 * Modul 1 — Akses Awal & Akun (TC-01..TC-07).
 * Catatan: TC-02 (daftar akun baru) dan TC-06 (lupa password) butuh email
 * unik / mailbox, jadi kita lakukan pengecekan UI saja, tidak submit form
 * yang membuat data baru di database.
 */

test.describe('Modul 1 — Akses Awal & Akun', () => {
    test('TC-01 @happy Akses landing tanpa login', async ({ page, failedRequests }) => {
        await page.goto('/');
        await waitReady(page);

        // Topbar Sign In / Masuk
        const signIn = page.getByRole('link', { name: /(sign in|masuk|login)/i }).first();
        await expect(signIn, 'Tombol Sign In harus muncul di topbar saat belum login.').toBeVisible();

        // Stats section: cari label uppercase "Pasien Terlayani"
        await expect(page.getByText(/pasien terlayani/i)).toBeVisible({ timeout: 15_000 });
        await expect(page.getByText(/jenis perawatan/i)).toBeVisible();
        await expect(page.getByText(/ahli kecantikan/i)).toBeVisible();
        await expect(page.getByText(/tahun berpengalaman/i)).toBeVisible();

        expect(failedRequests).toEqual([]);
    });

    test('TC-02 UI form daftar akun baru (tanpa submit)', async ({ page }) => {
        await page.goto('/register');
        await waitReady(page);

        await expect(page.getByLabel(/nama/i).first()).toBeVisible();
        await expect(page.getByLabel(/email/i).first()).toBeVisible();
        await expect(page.getByLabel(/(kata sandi|password)/i).first()).toBeVisible();

        // Toggle show/hide password (TC-123 cross-ref)
        const pwInput = page.getByLabel(/^kata sandi$|^password$/i).first();
        await pwInput.fill('ContohPass1');
        const typeBefore = await pwInput.getAttribute('type');
        expect(typeBefore).toBe('password');

        const eyeBtn = page.locator('button[aria-label*="sandi" i], button[aria-label*="password" i]').first();
        if (await eyeBtn.count()) {
            await eyeBtn.click();
            const typeAfter = await pwInput.getAttribute('type');
            expect(typeAfter, 'Setelah klik mata, type harus jadi text.').toBe('text');
        }
    });

    test('TC-03 Daftar tanpa isi → masih di /register', async ({ page }) => {
        await page.goto('/register');
        await waitReady(page);

        // Tombol register pakai teks "Buat Akun"
        const submit = page.getByRole('button', { name: /(buat akun|daftar|sign up|register)/i }).first();
        await expect(submit).toBeVisible();
        await submit.click();
        await waitReady(page);

        // Tetap di /register karena HTML5 required atau validation server
        expect(page.url()).toContain('/register');
    });

    test('TC-04 / TC-05 Login UI + password salah', async ({ page }) => {
        await page.goto('/login');
        await waitReady(page);

        const emailField = page.getByLabel(/email/i).first();
        const passField = page.getByLabel(/(kata sandi|password)/i).first();
        const submit = page.getByRole('button', { name: /(masuk|sign in|log in)/i }).first();

        await expect(emailField).toBeVisible();
        await expect(passField).toBeVisible();
        await expect(submit).toBeVisible();

        // TC-05: password salah
        await emailField.fill('non.exist@aishi.test');
        await passField.fill('wrongpassword123');
        await submit.click();
        await waitReady(page);

        expect(page.url()).toMatch(/\/login/);
        const err = page.getByText(/(salah|tidak cocok|tidak terdaftar|invalid|gagal)/i).first();
        await expect(err).toBeVisible({ timeout: 10_000 });
    });

    test('TC-06 UI halaman lupa password tampil', async ({ page }) => {
        await page.goto('/forgot-password');
        await waitReady(page);
        await expect(page.getByLabel(/email/i).first()).toBeVisible();
        await expect(page.getByRole('button').first()).toBeVisible();
    });
});
