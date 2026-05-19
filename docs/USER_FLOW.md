# User Flow — Aishi Beauty (Sisi Pasien)

Dokumen ini menjabarkan alur penggunaan website Aishi Beauty dari sisi **user/pasien**.
Panel admin (Filament) **tidak** termasuk di sini.

> Konvensi: setiap titik utama ditandai dengan ✱. Sub-langkah, prasyarat, dan
> percabangan ada di bullet di bawahnya. Halaman yang dirujuk pakai notasi `route_name`.

---

## 1. Akses Awal & Akun

✱ **Kunjungi landing page** (`landing` — `/`)
- Bisa diakses tanpa login.
- Sudah lihat: hero, statistik real-time (pasien terlayani, treatment aktif, ahli kecantikan), preview produk, preview treatment, form pra-konsultasi shortcut, FAQ.

✱ **Daftar akun baru** (`register`)
- Klik "Sign Up" di topbar → form: Nama, Email, Kata Sandi, Konfirmasi Sandi.
- Validasi unik email + sandi minimal 8 karakter (Bahasa Indonesia, password show/hide toggle).
- Setelah submit sukses → otomatis login → redirect ke `landing`.

✱ **Login** (`login`)
- Email + password, opsi "Ingat saya".
- Lupa sandi → `password.request` (kirim tautan reset) → `password.reset` (set sandi baru).
- Konfirmasi sandi dipakai saat akses area sensitif (`password.confirm`).

✱ **Verifikasi email** (`verification.notice` — opsional, kalau diaktifkan)
- Tautan dikirim ke email user, klik untuk aktivasi.
- Bisa kirim ulang lewat tombol "Kirim Ulang Tautan Verifikasi".

---

## 2. Eksplorasi Katalog Publik

✱ **Lihat treatment** (`treatment.index` — `/treatment`)
- Banner hero + filter kategori (sidebar) + search + sort (Harga Termurah default, Harga Tertinggi, Terbaru, Nama A-Z).
- Grid 2/3/4 kolom responsif. Hover kartu (desktop) atau tap (mobile) → tombol "Buat Janji Temu".

✱ **Detail treatment** (`treatment.show` — `/treatment/{id}`)
- Foto besar + nama + harga `/sesi` + durasi + deskripsi.
- Tombol utama: **Buat Janji Temu** (langsung ke wizard booking-treatment).

✱ **Lihat produk** (`shop.index` — `/shop`)
- Sama dengan treatment: banner + filter kategori + filter ketersediaan (Tersedia / Stok Habis) + sort + search.
- Hover kartu → tombol "Tambah Keranjang".

✱ **Detail produk** (`shop.show` — `/shop/{id}`)
- Foto, kategori, status stok, harga, deskripsi, qty stepper.
- Submit form **Tambah ke Keranjang** → AJAX → modal sukses (tanpa redirect):
  - Tombol "Lanjut Belanja" (tutup modal, tetap di halaman)
  - Tombol "Lihat Keranjang" (ke `cart.index`)
- Badge cart count di navbar auto-update.

✱ **Lihat paket bundling** (`bundle.index`, `bundle.show`)
- Pola sama dengan treatment/shop. Untuk bundle isi treatment murni → tombol arah ke booking, untuk bundle berisi produk → tombol "Tambah ke Keranjang" (dapat juga modal sukses).

✱ **Forum tanya jawab** (`forum.index`, `forum.show`)
- Browse pertanyaan publik tanpa login.
- Tombol "Buat Pertanyaan" → kalau belum login, redirect login dulu, lalu balik ke `forum.create`.

---

## 3. Pra-Konsultasi (Online, Tanpa Datang)

✱ **Isi form Pra-Konsultasi** (`pra.create` — `/pra-konsul/buat`)
- Stepper 4 step: Identitas → Riwayat Kulit → Foto Wajah (3 angle) → Konfirmasi.
- Field: jenis kulit, golongan darah, riwayat penyakit, skincare yang dipakai, riwayat treatment, dll.
- Upload foto depan/kiri/kanan (max 1 MB per foto).

✱ **Submit pra-konsul** (`pra.store`)
- Form disimpan ke `patient_forms` dengan status awal `submitted`.
- User dialihkan ke dashboard tab Pra-Konsultasi dengan toast "Berhasil dikirim".

✱ **Tunggu review dokter** (asynchronous — admin)
- Dokter mereview di panel admin → set status `reviewed/direkomendasikan` + tulis rekomendasi treatment & produk.

✱ **Lihat hasil rekomendasi** (`dashboard?tab=prakonsul`)
- Notifikasi muncul di navbar saat status berubah.
- Detail rekomendasi: catatan dokter, list treatment & produk yang disarankan.
- Tiap item rekomendasi punya CTA langsung:
  - Treatment → "Buat Janji Temu" (auto select treatment di wizard).
  - Produk → "+ Keranjang" / "Beli Sekarang".
  - Bundle → "+ Keranjang" / "Beli Sekarang".

---

## 4. Booking Janji Temu

### 4.1 Pilih Tipe Layanan

✱ **Halaman Pilih Layanan** (`appointment.select` — `/appointment/pilih`)
- Dua pilihan: **Konsultasi Dokter** atau **Treatment**.

### 4.2 Pra-syarat

✱ **Validasi profil**
- Sebelum masuk wizard, controller cek `phone` dan `tanggal_lahir`.
- Kalau salah satu kosong → simpan URL tujuan ke session, redirect ke `dashboard`, **modal popup amber** muncul:
  - Heading "Lengkapi Profil Anda"
  - Tombol "Nanti Saja" (close)
  - Tombol "Lengkapi Sekarang" → auto switch tab profile + smooth scroll ke section data diri
- Setelah user save profil yang valid → otomatis kembali ke flow booking yang tertunda dengan toast sukses.

### 4.3 Booking Konsultasi

✱ **Wizard Konsultasi** (`appointment.konsultasi`)
- 5 step: Konsultasi → Dokter → Jadwal → Data Pasien → Konfirmasi.
- **Step 1**: pilih jenis (Konsultasi Awal vs Lanjutan).
- **Step 2**: pilih dokter aktif (foto, spesialis, jadwal praktik).
- **Step 3**: pilih tanggal (dalam 14 hari, hanya hari praktik dokter) lalu pilih jam (slot 30 menit dari `jadwal_praktiks`).
  - Slot yang **sudah lewat** hari ini → disabled (label "Lewat", abu-abu).
  - Slot yang **sudah diambil pasien lain** untuk dokter yang sama → disabled (label "Penuh", merah).
- **Step 4**: review data pasien (auto dari profil, opsional edit).
- **Step 5**: konfirmasi & submit.

✱ **Submit konsultasi** (`appointment.konsultasi.store`)
- Backend: lock pessimistic + cek tabrakan, **1 dokter+tanggal+jam = 1 pasien**.
- Sukses → flash success + modal dengan tombol "Lihat Janji Temu" (ke `dashboard?tab=appointment&highlight=...`).
- Slot bentrok (race condition) → response 422 + toast "Slot ini baru saja diambil pasien lain", auto-refresh slot, kembali ke step Jadwal.

### 4.4 Booking Treatment

✱ **Wizard Booking Treatment** (`appointment.booking-treatment`)
- 4 step: Layanan → Jadwal → Data Diri → Pembayaran.
- **Step 1 — Pilih Layanan**:
  - Filter kategori, search, info-box pra-konsultasi gratis.
  - Tap kartu untuk lihat detail (foto, deskripsi). Tombol "Tambahkan ke Pilihan".
  - Sticky bar bawah menampilkan jumlah & total layanan terpilih.
- **Step 2 — Jadwal**:
  - Pilih tanggal (14 hari ke depan), lalu jam dari endpoint `appointment.booking-treatment.check-slots` (slot 2 jam: 08:00–10:00, 10:30–12:30, 13:00–15:00, 15:30–17:30).
  - Setiap slot punya kapasitas **2 pasien**. Kalau penuh → label "Penuh" merah.
- **Step 3 — Data Diri**: auto dari profil, peringatan kalau profil belum lengkap.
- **Step 4 — Pembayaran**:
  - Detail rincian (jadwal + items + subtotal + total).
  - Tombol "Bayar Sekarang" → buka Midtrans Snap.

✱ **Submit booking treatment** (`appointment.booking-treatment.store`)
- Backend: validasi slot kapasitas dengan `lockForUpdate`, buat `Appointment` + `AppointmentItem` + `Order` + `OrderItem`, generate `kode_order` `ORD-YYYYMMDD-XXX`, generate Snap token Midtrans.
- Response sukses → JS panggil `snap.pay(token)`:
  - `onSuccess` / `onPending` → ke `dashboard?tab=appointment&highlight={id}`.
  - `onError` → toast error.
  - `onClose` → reset state submit (user bisa coba lagi).
- Kalau Midtrans gangguan → fallback `success_no_payment` (booking tetap dibuat, klinik dihubungi manual).

---

## 5. Belanja Produk & Bundle

### 5.1 Keranjang Belanja

✱ **Lihat keranjang** (`cart.index` — `/cart`)
- List item (produk + bundle), qty stepper, subtotal per item, tombol Hapus.
- **Untuk item bundle**: di card cart muncul box "Isi Paket" dengan rincian produk/treatment di dalamnya.
- Sidebar **Ringkasan Belanja**: detail item per baris (nama + qty pill + subtotal), total item, subtotal grand total, tombol "Lanjut ke Pembayaran".
- Empty state ramah dengan CTA "Mulai Belanja Sekarang".

### 5.2 Checkout

✱ **Halaman checkout** (`checkout.index`)
- Validasi awal: keranjang tidak kosong, alamat user lengkap (province/city/district + kode_pos).
- Form:
  - Pilih alamat pengiriman (otomatis dari profil).
  - Pilih kurir & layanan via API `api.checkOngkir` (RajaOngkir/Komerce). Ongkir update real-time saat user ganti layanan.
  - Input voucher promo via `api.checkPromo` (kalau valid → diskon dikurangi).
  - Catatan pesanan (opsional).
- Ringkasan: subtotal, diskon, ongkir, grand total.

✱ **Submit checkout** (`checkout.store`)
- Backend: buat `Order` dengan kode `ORD-YYYYMMDD-XXX`, generate Snap token, redirect ke `payment.show`.

### 5.3 Pembayaran

✱ **Halaman pembayaran** (`payment.show` — `/payment/{order}`)
- Tampil kode order, total, countdown 15 menit.
- Tombol "Bayar Sekarang" buka Midtrans Snap modal.

✱ **Outcome pembayaran**
- **Success** (`payment.success`): redirect ke `dashboard?tab=orders&highlight={id}` + flash success.
- **Pending** (`payment.unfinish`): redirect ke `dashboard?tab=orders` + flash warning "Pembayaran belum selesai. Lanjutkan dari halaman pesanan."
- **Error** (`payment.error`): redirect ke `dashboard?tab=orders` + flash error.
- **Webhook** (`midtrans.notification`): server-to-server, update status order otomatis (`menunggu_pembayaran` → `sudah_dibayar`/`lunas`/`batal`).

✱ **Pembayaran tertunda di tab Pesanan**
- User bisa klik "Bayar" di tab `orders` untuk dapatkan token baru via `payment.token` (AJAX) lalu retry Snap.

---

## 6. Pendaftaran Member

✱ **Form member** (`member.register` — `/member/daftar`)
- Hanya untuk user yang belum jadi member.
- Form medis: golongan darah, jenis kulit, keluhan, riwayat skincare & treatment + nomor HP (kalau belum diisi).

✱ **Submit member** (`member.store`)
- Backend: simpan `PatientForm` tipe `registrasi_member`, buat `Order` membership (Rp 50.000) dengan kode `MEM-YYYYMMDD-XXXX`.
- Redirect ke `payment.show` → bayar via Midtrans.
- Setelah pembayaran sukses (via webhook): `is_member=true` di profile pasien, generate `kode_rm`.

---

## 7. Dashboard Pasien

✱ **Tab Overview** (`dashboard?tab=overview`, default)
- Stats card: total appointment, pending appointment, total order, total spent.
- Section **Jadwal Mendatang** (3 appointment terdekat dengan status diajukan/disetujui).
- Section **Aktivitas Terkini** (1 pra-konsul + 1 order terbaru). Order yang berasal dari treatment/konsultasi → tombol "Lihat Janji Temu" (ke tab appointment), produk biasa → "Lihat Pesanan" (ke tab orders).

✱ **Tab Janji Temu** (`?tab=appointment`)
- Filter: status (diajukan / disetujui / selesai / batal / tidak_hadir / aktif), jenis (treatment / konsultasi), payment status, date range.
- Sort: tanggal terdekat / terlama.
- Tiap kartu appointment:
  - Detail (jenis, jam, dokter/terapis, items, total biaya, status).
  - Kalau lunas + treatment selesai → bisa klik kartu untuk lihat **Nota Treatment** (modal full-screen).
  - Tombol **Batalkan Janji** (kalau status diajukan/disetujui & belum H-3 jam).
  - Tombol **Ubah Jadwal** (sebelum H-3 jam):
    - Modal pilih tanggal & jam baru.
    - Treatment: slot 2 jam dari endpoint check-slots.
    - Konsultasi: slot 30 menit dari jadwal dokter.
    - Submit → status reset ke `diajukan` + admin dapat notif.

✱ **Tab Pra-Konsultasi** (`?tab=prakonsul`)
- List form pra-konsul yang pernah dikirim, status, dan rekomendasi dokter (kalau sudah direview).
- CTA langsung ke "Buat Janji Temu" untuk treatment yang direkomendasikan.

✱ **Tab Pesanan Saya** (`?tab=orders`)
- List order produk + bundle (bukan treatment).
- Filter status & tanggal.
- Aksi:
  - **Bayar Sekarang** (kalau menunggu pembayaran) — buka Snap modal.
  - **Email Invoice** (kirim PDF nota ke email pasien) — `invoice.email`.
  - **Selesaikan Pesanan** (kalau status `dikirim`) — `orders.complete`.
  - **Batalkan** (kalau status `menunggu_pembayaran`) — `orders.cancel`.

✱ **Tab Rekam Medis** (`?tab=rekam_medis`)
- Riwayat kunjungan klinik (treatment yang sudah selesai).
- Detail no_kunjungan, dokter/terapis, treatment yang dilakukan, catatan medis, foto sebelum/sesudah (jika ada).

✱ **Tab Akun Saya** (`?tab=profile`, `profile_data`/`profile_password`/`profile_alamat`)
- Sub-tab Data Diri: nama, email (read-only), nomor WA, jenis kelamin, tanggal lahir, foto profil (max 1 MB).
- Sub-tab Ganti Password: password saat ini + baru + konfirmasi (semua input punya toggle show/hide).
- Sub-tab Alamat: pilih provinsi → kota → kecamatan via API, kode pos, alamat detail. Dipakai untuk ongkir.
- Profile dirty detector: kalau ada perubahan belum disave, switch tab atau close window minta konfirmasi.

---

## 8. Notifikasi & Komunikasi

✱ **In-app notifications** (database notifications)
- Bell icon di topbar dengan badge unread count.
- Klik notif → mark as read (`notification.read`) + redirect ke konteksnya.
- Tombol "Tandai semua dibaca" (`notification.readAll`).

✱ **Trigger notifikasi**:
- Appointment status berubah (diajukan → disetujui / batal / selesai).
- Pra-konsul direview oleh dokter.
- Pesanan dikemas / dikirim / waybill diupdate.
- Pembayaran dikonfirmasi.

✱ **Email**
- Reset password.
- Invoice nota (manual via tombol "Email Invoice" atau auto setelah selesai treatment).

✱ **WhatsApp** (widget di pojok kanan bawah)
- Klik logo WhatsApp → buka chat dengan template "Halo Aishi Beauty, saya ingin bertanya tentang layanan klinik."

---

## 9. Forum Tanya Jawab

✱ **Lihat forum** (`forum.index`) — publik.
✱ **Detail pertanyaan** (`forum.show`) — publik.
✱ **Buat pertanyaan baru** (`forum.create` → `forum.store`) — wajib login.
- Tampilkan pertanyaan + jawaban dokter yang sudah di-publish admin.

---

## 10. Logout & Selesai

✱ **Logout** (`logout`)
- Klik avatar di topbar → "Log Out" (atau di sidebar dashboard).
- Session destroyed, redirect ke `landing`.

---

## Diagram Ringkas

```
[Landing] ──┬─► [Register] ──► [Login] ──► [Dashboard]
            │                                  │
            ├─► [Login] ───────────────────────┤
            │                                  ├─► [Tab Overview]
            ├─► [Browse Treatment / Shop]      ├─► [Tab Janji Temu]
            │                                  ├─► [Tab Pra-Konsultasi]
            │     │                            ├─► [Tab Pesanan]
            │     ├─► [Add to Cart] ──► [Cart] ─► [Checkout] ──► [Payment] ──► [Dashboard?tab=orders]
            │     │                                                                ▲
            │     └─► [Buat Janji] ──► [Profil OK?] ──Yes─► [Wizard Booking] ──┐  │
            │                              │                                  │  │
            │                              └─No─► [Modal Lengkapi Profil]     │  │
            │                                          │                      │  │
            │                                          └─► [Save Profil] ─────┘  │
            │                                                                    │
            ├─► [Pra-Konsul] ──► [Submit] ──► [Tunggu Review] ──► [Rekomendasi] ─┤
            │                                                                    │
            └─► [Forum] ──► [Login] ──► [Buat Pertanyaan]                       │
                                                                                 │
                                                            [Pembayaran Sukses]──┘
```

---

## Catatan Implementasi Penting

- **Profil wajib lengkap** (`phone` + `tanggal_lahir`) sebelum booking apa pun. Dijaga via session redirect + modal global.
- **Slot uniqueness**: 1 dokter + tanggal_jam = 1 pasien (konsultasi). Treatment slot kapasitas 2 pasien per slot 2-jam.
- **Reschedule limit**: H-3 jam sebelum jadwal. Lewat → harus hubungi admin via WhatsApp.
- **Order code**: `ORD-YYYYMMDD-XXX` untuk transaksi reguler, `MEM-YYYYMMDD-XXXX` untuk membership (lihat `App\Support\OrderCodeGenerator`).
- **Locale**: aplikasi pakai Bahasa Indonesia di seluruh UI termasuk validasi & email. Carbon translatedFormat aktif.
- **Modal global** terdaftar di `layouts/guest.blade.php`: `<x-toast>`, `<x-confirm-dialog>`, `<x-cart-success-modal>`. Di dashboard tambah `<x-profile-required-modal>`.
