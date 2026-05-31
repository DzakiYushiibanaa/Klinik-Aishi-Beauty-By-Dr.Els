# Aishi Beauty — Playwright QA E2E

Otomatisasi QA test untuk sisi user/pasien berdasarkan
[`docs/QA_TEST_GUIDE.md`](../docs/QA_TEST_GUIDE.md).

## Setup

```cmd
cd tests-e2e
npm install
npx playwright install chrome
```

## Konfigurasi

Buat file `.env.local` (opsional) untuk override base URL:

```
BASE_URL=https://dev.aishibeauty.my.id
```

Untuk login otomatis, set credential test yang sudah disiapkan developer:

```
TEST_USER_EMAIL=qa.user@aishi.test
TEST_USER_PASSWORD=qa-password-123
```

> Akun QA harus disiapkan developer lewat panel admin atau register manual.
> Jangan seed/insert lewat database.

## Menjalankan Test

```cmd
:: Semua spec (Chrome desktop)
npm test

:: Headed (browser terlihat)
npm run test:headed

:: Mode UI interaktif
npm run test:ui

:: Filter modul
npx playwright test tests/01-auth.spec.js

:: Hanya happy path
npx playwright test --grep @happy

:: Mobile (Pixel 7)
npx playwright test --project=chrome-mobile
```

## Lihat Laporan

```cmd
npm run report
```

Akan membuka HTML report di browser dengan trace, screenshot, dan video.

## Catatan QA

- Tidak ada test yang menyentuh database / phpMyAdmin / tinker.
- Semua test berinteraksi via UI saja.
- Test yang butuh state khusus (mis. order lunas, akun di-disable) ditandai
  `test.skip` dengan TODO note. Minta developer siapkan data lalu hapus skip-nya.
- Midtrans Snap hanya divalidasi sampai modal muncul, bukan sampai pembayaran
  selesai.
