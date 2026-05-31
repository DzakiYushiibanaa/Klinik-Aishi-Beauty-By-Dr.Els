# Panduan QA Test — Aishi Beauty (Sisi User/Pasien)

> Dokumen ini untuk **QA Junior** yang baru bergabung. Berisi seluruh fitur user
> yang perlu dites manual, lengkap dengan langkah, ekspektasi, dan edge case.
> Panel admin tidak termasuk. Pendaftaran member di-skip dulu.

## Aturan Penting QA

- **Tes hanya di dalam website**. Jangan pernah membuka / mengubah database
  langsung (phpMyAdmin, tinker, query SQL, dll).
- **Jangan reset, hapus, atau seed ulang database**. Kalau test case butuh
  state tertentu (mis. order lunas, form sudah direview), gunakan akun yang
  datanya **sudah disiapkan** developer. Jangan bersihkan data.
- **Jangan rename/ubah/hapus** akun, order, appointment lewat panel admin
  kecuali test case eksplisit memintanya. Kalau ragu, tanya developer dulu.
- Semua verifikasi dilakukan dengan **mata** (UI) dan **interaksi** (klik,
  isi form, submit). Tidak boleh inspect DB.

## Cara Pakai Dokumen Ini

- Setiap test case punya **ID** (`TC-XX`), **Pre-condition**, **Langkah**, dan
  **Expected Result**.
- Tandai status: ✅ Lulus | ❌ Gagal | ⚠️ Bug minor.
- Kalau menemukan bug, catat: ID test case, browser & device, screenshot,
  langkah reproduksi.
- Selalu test minimal di **2 device**: desktop (Chrome/Firefox) + mobile
  (375px width via DevTools atau HP fisik).
- Kalau pre-condition butuh data khusus (mis. "form sudah direview",
  "appointment status disetujui"), minta developer **menyiapkan akun
  testing** dengan data tersebut. **Jangan** menyiapkan sendiri lewat
  database / tinker.

---

## Modul 1 — Akses Awal & Akun

### TC-01 — Akses landing tanpa login
- **Pre**: belum login (incognito).
- **Langkah**: buka `/`.
- **Expected**:
  - Hero, statistik (pasien terlayani, jenis perawatan, ahli kecantikan, tahun pengalaman) muncul dengan animasi count-up.
  - Tombol "Sign In" / "Sign Up" muncul di topbar (bukan avatar).
  - Tidak ada error console.

### TC-02 — Daftar akun baru
- **Pre**: belum punya akun.
- **Langkah**:
  1. Klik **Sign Up** di topbar.
  2. Isi Nama, Email, Kata Sandi (≥8 karakter), Konfirmasi Sandi.
  3. Klik tombol mata di kanan field sandi → password muncul/tersembunyi.
  4. Submit.
- **Expected**:
  - Validasi sukses → otomatis login → redirect ke landing.
  - Avatar + nama tampil di topbar.

### TC-03 — Daftar dengan email sudah dipakai
- **Pre**: ada akun email `test@aishi.com` aktif.
- **Langkah**: register dengan email yang sama.
- **Expected**: muncul error "Email sudah terdaftar. Silakan gunakan email lain."

### TC-04 — Login berhasil
- **Pre**: punya akun.
- **Langkah**: buka `/login` → email + password → submit.
- **Expected**: redirect ke landing, navbar tampil avatar.

### TC-05 — Login dengan password salah
- **Pre**: punya akun.
- **Langkah**: login dengan password salah.
- **Expected**: pesan error "Email atau password yang Anda masukkan salah." muncul di bawah field email (tanpa toast duplikat).

### TC-06 — Lupa password
- **Pre**: punya akun.
- **Langkah**:
  1. Login page → klik "Lupa sandi?".
  2. Isi email → submit.
  3. Cek email (di Mailtrap kalau dev).
  4. Klik link → set sandi baru → konfirmasi sandi → submit.
- **Expected**: redirect ke login dengan toast sukses, sandi lama tidak berlaku.

### TC-07 — Logout
- **Pre**: sudah login.
- **Langkah**: klik avatar di topbar (atau sidebar dashboard) → "Log Out".
- **Expected**: session destroyed, redirect ke landing, navbar kembali Sign In/Sign Up.

---

## Modul 2 — Katalog Publik

### TC-10 — Browse Treatment
- **Langkah**: buka `/treatment`.
- **Expected**:
  - Banner hero + breadcrumb.
  - Filter kategori (sidebar) + counter hasil.
  - Sort default = **Harga Termurah**, urut kecil ke besar.
  - Grid: 2 kol di mobile, 3 kol di laptop, 4 kol di desktop.
  - Hover kartu desktop → tombol "Buat Janji Temu" muncul.
  - Tap kartu mobile → ada CTA bar permanen.

### TC-11 — Sort Treatment
- **Langkah**: pilih dropdown sort → coba semua opsi.
- **Expected**:
  - "Harga Tertinggi": item paling mahal di atas.
  - "Terbaru": id terbesar dulu.
  - "Nama A-Z": alfabet.
  - URL berubah ada `?sort=...`, refresh tetap konsisten.

### TC-12 — Filter Treatment by kategori
- **Langkah**: centang 1 kategori di sidebar.
- **Expected**:
  - Submit auto, hanya item kategori itu muncul.
  - Counter hasil update.
  - Sort sebelumnya tetap aktif.
  - Chip "Aktif" muncul di area filter aktif.

### TC-13 — Search treatment
- **Langkah**: ketik keyword di search bar → Enter.
- **Expected**: hanya treatment yang nama mengandung keyword (case-insensitive). Filter kategori sebelumnya tetap.

### TC-14 — Detail Treatment
- **Langkah**: klik kartu treatment.
- **Expected**:
  - Layout 2 kolom (foto kiri, info kanan) di desktop, stack di mobile.
  - Nama, kategori, harga `/sesi`, durasi, deskripsi, status (Tersedia/Tidak).
  - Tombol "Buat Janji Temu".

### TC-15 — Browse Shop
- **Langkah**: buka `/shop`.
- **Expected**: sama dengan treatment + filter Ketersediaan (Tersedia / Stok Habis), badge stok rendah/habis di kartu.

### TC-16 — Detail produk + Tambah Keranjang
- **Pre**: sudah login.
- **Langkah**:
  1. Buka detail produk dengan stok > 0.
  2. Naik-turunkan qty (stepper).
  3. Klik **Tambah ke Keranjang**.
- **Expected**:
  - **Tidak redirect**.
  - Modal popup centered muncul: ikon centang emerald, "Berhasil Ditambahkan", nama produk.
  - Badge keranjang di navbar update +1.
  - Klik "Lanjut Belanja" → modal tutup, tetap di halaman produk.
  - Klik "Lihat Keranjang" → ke `/cart`.

### TC-17 — Tambah Keranjang saat belum login
- **Pre**: belum login.
- **Langkah**: klik Tambah ke Keranjang dari halaman katalog.
- **Expected**: redirect ke `/login`, setelah login dikembalikan ke halaman sebelumnya.

### TC-18 — Browse Bundle
- **Langkah**: buka `/bundle`.
- **Expected**: filter jenis (Produk/Treatment/Kombinasi) + sort termasuk "Diskon Terbesar". Card menampilkan diskon ribbon kalau ada `harga_promo`.

---

## Modul 3 — Pra-Konsultasi

### TC-20 — Submit Pra-Konsul
- **Pre**: login + profil minimal terisi.
- **Langkah**:
  1. Buka `/pra-konsul/buat`.
  2. Isi 4 step: identitas, riwayat kulit, foto wajah (3 angle, max 1 MB), konfirmasi.
  3. Submit.
- **Expected**: redirect ke dashboard tab Pra-Konsultasi, toast sukses, status form `submitted`.

### TC-21 — Upload foto > 1 MB
- **Langkah**: pilih foto besar.
- **Expected**: validasi error "Ukuran gambar maksimal 1 MB", file tidak ter-attach.

### TC-22 — Lihat hasil rekomendasi
- **Pre**: form sudah direview admin (status `direkomendasikan` + isi rekomendasi). **Minta developer menyiapkan akun testing dengan kondisi ini**.
- **Langkah**: buka dashboard tab Pra-Konsultasi.
- **Expected**:
  - Kartu menampilkan catatan dokter, list treatment & produk yang direkomendasikan.
  - Tombol "Buat Janji Temu" untuk treatment → membuka wizard dengan auto-select.
  - Tombol "+ Keranjang" untuk produk/bundle → modal sukses.

---

## Modul 4 — Booking Janji Temu

### TC-30 — Cek validasi profil sebelum booking
- **Pre**: login dengan akun yang `phone` atau `tanggal_lahir` kosong.
- **Langkah**: buka `/treatment/{id}` → klik "Buat Janji Temu".
- **Expected**:
  - Redirect ke `/dashboard`, tab Profil otomatis aktif.
  - **Modal popup amber** muncul: "Lengkapi Profil Anda" dengan dua tombol.
  - **Tidak ada toast error duplikat** di kanan atas.

### TC-31 — Klik "Lengkapi Sekarang" di modal
- **Langkah**: dari TC-30 klik tombol gold di modal.
- **Expected**:
  - Modal tutup.
  - Smooth scroll otomatis ke section Data Diri.
  - Form profil siap diedit.

### TC-32 — Save profil → otomatis kembali ke booking
- **Pre**: lanjut dari TC-31.
- **Langkah**:
  1. Isi Nomor WhatsApp + Tanggal Lahir.
  2. Save Profil.
- **Expected**:
  - Toast sukses "Profil berhasil diperbarui. Silakan lanjutkan janji temu Anda."
  - Redirect otomatis ke wizard booking yang tadi dituju (bukan tetap di dashboard).

### TC-33 — Klik "Nanti Saja" di modal profil
- **Langkah**: dari TC-30 klik tombol outline.
- **Expected**: modal tutup, user tetap di tab Profil dashboard, intended URL hilang. Coba booking lagi → modal muncul lagi.

### TC-34 — Wizard Booking Treatment Step 1 (Pilih Layanan)
- **Pre**: profil lengkap.
- **Langkah**:
  1. Buka `/appointment/booking-treatment` (atau dari deep link kartu treatment).
  2. Tab kategori, search.
  3. Pilih 1+ treatment.
- **Expected**:
  - Treatment terpilih ditandai gold border.
  - Sticky bar bawah muncul dengan jumlah & total harga.
  - Tombol "Lanjut Jadwal" aktif.

### TC-35 — Step 2 (Jadwal) — Treatment
- **Langkah**:
  1. Klik "Lanjut Jadwal".
  2. Pilih tanggal (14 hari ke depan, semua hari).
  3. Slot 2-jam muncul: 08:00–10:00, 10:30–12:30, 13:00–15:00, 15:30–17:30.
  4. Pilih slot.
- **Expected**:
  - Slot menampilkan label "Sisa N slot" / "Penuh" / "Lewat".
  - Slot Penuh / Lewat tidak bisa diklik.
  - Tombol "Lanjut Data Diri" aktif.

### TC-36 — Step 3 (Data Diri)
- **Langkah**: review data otomatis dari profil → "Lanjut Pembayaran".
- **Expected**: data tampil read-only. Kalau profil tidak lengkap, ada warning + link ke dashboard.

### TC-37 — Step 4 (Pembayaran)
- **Langkah**: review rincian → "Bayar Sekarang".
- **Expected**:
  - Midtrans Snap modal muncul.
  - Pilih metode (Bank Transfer/QRIS/dll), bayar (sandbox).
  - On success → redirect ke `dashboard?tab=appointment&highlight={id}` dengan kartu di-highlight ring gold.

### TC-38 — Wizard Konsultasi Step Dokter & Jadwal
- **Pre**: profil lengkap.
- **Langkah**:
  1. Buka `/appointment/konsultasi`.
  2. Pilih jenis (Awal/Lanjutan).
  3. Pilih dokter aktif (status_praktik = aktif).
  4. Pilih tanggal — hanya hari praktik dokter yang aktif yang muncul.
  5. Pilih jam (slot 30 menit dari `jadwal_praktiks`).
- **Expected**:
  - Slot yang sudah lewat → label "Lewat", abu-abu, disabled.
  - Slot yang sudah dibooking pasien lain di dokter sama → label "Penuh", merah, disabled.

### TC-39 — Race condition slot konsultasi
- **Pre**: 2 user (browser session berbeda) di-step Jadwal yang sama.
- **Langkah**:
  1. User A pilih dokter + tanggal + jam X → submit.
  2. User B pilih kombinasi sama yang sudah dipilih A → submit.
- **Expected**: User B dapat pesan "Slot ini baru saja diambil pasien lain. Silakan pilih jam lain.", auto-refresh slot, kembali ke step Jadwal.

### TC-40 — Submit booking treatment race
- **Pre**: dua user pilih slot 2-jam yang sama (kapasitas 2 pasien).
- **Langkah**: keduanya submit hampir bersamaan setelah slot punya 1 booking aktif.
- **Expected**: salah satu sukses, satu lagi dapat error "Slot baru saja penuh. Silakan pilih jadwal lain."

---

## Modul 5 — Reschedule Janji Temu

### TC-50 — Reschedule appointment treatment
- **Pre**: punya appointment treatment status `diajukan` atau `disetujui`, tanggal masih > H-3 jam.
- **Langkah**:
  1. Dashboard → tab Janji Temu.
  2. Klik tombol **Ubah Jadwal** di kartu appointment.
  3. Modal "Ubah Jadwal" muncul.
  4. Pilih tanggal baru (max 14 hari ke depan).
  5. Pilih slot 2-jam baru.
  6. Klik "Lanjutkan".
  7. Modal Konfirmasi Perubahan muncul, klik "Ya, Ubah Jadwal".
- **Expected**:
  - Status appointment kembali ke `diajukan` (perlu approval admin lagi).
  - Notifikasi muncul di admin panel.
  - Toast sukses: "Pengajuan ubah jadwal berhasil. Menunggu konfirmasi admin."

### TC-51 — Reschedule appointment konsultasi
- **Pre**: punya appointment konsultasi.
- **Langkah**: sama dengan TC-50, tapi modal menampilkan slot 30 menit dari jadwal dokter.
- **Expected**:
  - Heading kolom slot: "Pilih Jam Baru (WIB)" (bukan "Pilih Slot Treatment").
  - Pesan kontekstual: "Pilih jadwal baru sesuai hari praktik dokter Anda."
  - Slot generated dari `jadwal_praktiks` user_id dokter.

### TC-52 — Reschedule lewat batas H-3 jam
- **Pre**: appointment dengan `tanggal_jam < now + 3 jam`.
- **Langkah**: klik Ubah Jadwal.
- **Expected**: error toast "Batas waktu perubahan jadwal sudah lewat (maksimal 3 jam sebelumnya). Silakan hubungi admin via WhatsApp." Tombol tidak proceed.

### TC-53 — Reschedule appointment yang sudah selesai/batal
- **Pre**: appointment status `selesai` / `batal` / `tidak_hadir`.
- **Langkah**: cari tombol Ubah Jadwal.
- **Expected**: tombol **tidak muncul** (hanya untuk status diajukan/disetujui).

### TC-54 — Modal reschedule click-stop
- **Pre**: TC-50 step 3.
- **Langkah**: klik area tanggal/slot di dalam modal.
- **Expected**: modal tetap terbuka, klik **tidak menembus** ke kartu di belakang (tidak buka modal Nota Treatment).

### TC-55 — Modal reschedule click-away
- **Langkah**: klik backdrop hitam di luar panel modal.
- **Expected**: modal tertutup, state newTanggal/newJam reset.

### TC-56 — Tanggal terkirim sesuai pilihan (timezone fix)
- **Pre**: device timezone WIB (UTC+7), waktu lokal pagi.
- **Langkah**: di modal reschedule pilih "SEN 18 Mei", lanjut, konfirmasi.
- **Expected**: backend menyimpan tanggal `2026-05-18` (bukan mundur jadi `2026-05-17`).

---

## Modul 6 — Pembatalan Janji Temu

### TC-60 — Cancel appointment normal
- **Pre**: appointment status `diajukan` atau `disetujui`, masih di masa depan.
- **Langkah**:
  1. Dashboard → tab Janji Temu.
  2. Klik **Batalkan Janji** di kartu.
  3. Modal "Batalkan Janji?" muncul.
  4. Pilih alasan dari dropdown atau ketik manual.
  5. Klik "Ya, Batalkan".
- **Expected**:
  - Status appointment update ke `batal`.
  - `canceled_by = pasien`, `cancel_reason` tersimpan.
  - Toast sukses: "Janji temu berhasil dibatalkan."
  - Admin dapat notifikasi pembatalan.

### TC-61 — Cancel tanpa alasan
- **Langkah**: klik "Ya, Batalkan" tanpa isi alasan.
- **Expected**: validasi error "Mohon isi alasan pembatalan." atau alasan default tersimpan, sesuai implementasi.

### TC-62 — Cancel appointment yang sudah selesai/batal
- **Pre**: status final.
- **Expected**: tombol Batalkan **tidak muncul**.

### TC-63 — Cancel via modal click-away
- **Langkah**: buka modal Batalkan, klik backdrop.
- **Expected**: modal tertutup, tidak ada perubahan data.

### TC-64 — Cancel sambil ada Reschedule modal
- **Pre**: TC-50 sudah buka modal Reschedule.
- **Langkah**: tutup modal Reschedule → klik Batalkan.
- **Expected**: modal Cancel terbuka clean, modal Reschedule sudah tertutup, state masing-masing independen.

---

## Modul 7 — Belanja Produk & Keranjang

### TC-70 — Lihat keranjang berisi item
- **Pre**: ada produk + bundle di cart.
- **Langkah**: buka `/cart`.
- **Expected**:
  - Tiap item: foto contain (tidak ke-crop), nama, harga, qty stepper, subtotal, tombol Hapus.
  - **Khusus bundle**: di card muncul box "Isi Paket" dengan list produk/treatment di dalamnya + qty pill.
  - Sidebar Ringkasan Belanja menampilkan: list detail item (qty pill di bawah nama, subtotal kanan), total item, subtotal, total harga.

### TC-71 — Update qty cart
- **Langkah**: klik +/- di stepper item.
- **Expected**: form auto-submit, halaman reload, qty + subtotal update.

### TC-72 — Hapus item cart
- **Langkah**: klik Hapus.
- **Expected**: item dihapus dari cart, toast sukses, total update.

### TC-73 — Cart kosong
- **Pre**: tidak ada item di cart.
- **Langkah**: buka `/cart`.
- **Expected**: empty state ramah dengan ikon dan tombol "Mulai Belanja Sekarang" → ke `/shop`.

### TC-74 — Tambah produk yang sudah ada
- **Pre**: produk X sudah di cart qty 1.
- **Langkah**: dari katalog, tambah produk X lagi.
- **Expected**: qty diperbarui (jadi 2 atau lebih), modal sukses tampil dengan pesan "Jumlah produk di keranjang Anda diperbarui."

---

## Modul 8 — Checkout & Pembayaran

### TC-80 — Akses checkout dengan cart kosong
- **Langkah**: cart kosong → akses `/checkout` langsung.
- **Expected**: redirect ke `/cart` dengan flash error "Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu."

### TC-81 — Akses checkout tanpa alamat
- **Pre**: cart isi tapi alamat profil tidak lengkap (tanpa district_id).
- **Langkah**: klik "Lanjut ke Pembayaran" dari cart.
- **Expected**: redirect ke dashboard tab Profil dengan flash "Mohon lengkapi alamat di profil Anda sebelum melanjutkan pembayaran."

### TC-82 — Checkout normal
- **Pre**: cart isi, alamat lengkap.
- **Langkah**:
  1. `/checkout`.
  2. Pilih kurir + layanan → ongkir update.
  3. (Opsional) input voucher → diskon update.
  4. (Opsional) catatan.
  5. Submit.
- **Expected**: redirect ke `/payment/{order_id}` dengan kode order `ORD-YYYYMMDD-XXX`.

### TC-83 — Voucher invalid
- **Langkah**: input kode voucher salah.
- **Expected**: pesan error muncul di sebelah input, tidak ada diskon.

### TC-84 — Bayar via Midtrans Snap
- **Langkah**:
  1. `/payment/{order_id}` → "Bayar Sekarang".
  2. Pilih metode di Snap (sandbox: Bank BCA dummy).
  3. Lengkapi pembayaran.
- **Expected**:
  - Webhook `midtrans.notification` memperbarui order ke `sudah_dibayar` / `lunas`.
  - Redirect ke `/dashboard?tab=orders&highlight=...` dengan toast sukses.

### TC-85 — Tutup popup Snap tanpa bayar
- **Langkah**: buka Snap → close.
- **Expected**: tetap di halaman payment, status order tidak berubah, user bisa retry.

### TC-86 — Pembayaran tertunda (pending)
- **Langkah**: pilih VA tapi tidak transfer dalam 15 menit.
- **Expected**:
  - Status order: `menunggu_pembayaran`, `expired_at` tercatat.
  - User bisa kembali lewat tab Pesanan dan klik "Bayar" untuk retry token baru.

### TC-87 — Bayar dari tab Pesanan (retry)
- **Pre**: ada order `menunggu_pembayaran`.
- **Langkah**: dashboard tab Pesanan → klik "Bayar Sekarang" di kartu order.
- **Expected**: AJAX ke `payment.token` ambil snap_token baru, Snap modal terbuka.

### TC-88 — Cancel order pending
- **Langkah**: tab Pesanan → klik "Batalkan" di order pending.
- **Expected**: modal konfirmasi → "Ya, Batalkan" → status `dibatalkan`, stok produk dikembalikan, toast sukses.

### TC-89 — Selesaikan order setelah dikirim
- **Pre**: order status `dikirim`.
- **Langkah**: klik "Selesaikan Pesanan".
- **Expected**: status `selesai`, toast "Terima kasih, pesanan Anda telah ditandai selesai."

### TC-90 — Email invoice
- **Pre**: order sudah lunas.
- **Langkah**: klik "Email Invoice" di kartu order.
- **Expected**: AJAX, toast "Email berhasil dikirim", cek mailtrap (dev) ada PDF invoice attachment.

---

## Modul 9 — Dashboard Tabs

### TC-100 — Tab Overview
- **Langkah**: buka `/dashboard` polos.
- **Expected**:
  - Default tab "Overview" aktif.
  - Stats card (total appt, upcoming, total order, spent).
  - Section Jadwal Mendatang: kartu appointment terdekat dengan footer rapi (status pill kiri, tombol Lihat Detail kanan, tidak wrap).
  - Section Aktivitas Terkini: 1 pra-konsul + 1 order. Order treatment/konsultasi → tombol "Lihat Janji Temu" (tab appointment, highlight). Order produk → "Lihat Pesanan".

### TC-101 — Filter tab Janji Temu
- **Langkah**: tab Janji Temu → coba semua filter (status, jenis, payment, date range).
- **Expected**: hasil filter sesuai, count update real-time, sort terdekat/terlama bekerja.

### TC-102 — Detail Nota Treatment
- **Pre**: appointment treatment status selesai + order lunas.
- **Langkah**: klik kartu appointment.
- **Expected**: modal Nota Treatment full-screen muncul dengan rincian + tombol cetak/email.

### TC-103 — Profile dirty detector
- **Langkah**: tab Profil → ubah salah satu field tanpa save → coba switch tab lain.
- **Expected**: dialog konfirmasi "Perubahan belum disimpan, yakin ingin pindah?".

### TC-104 — Ganti password
- **Langkah**: tab Akun → sub Ganti Password → password lama + baru + konfirmasi → save.
- **Expected**: toast sukses, sandi lama tidak berlaku saat re-login. Toggle show/hide bekerja di 3 input.

### TC-105 — Pilih alamat (cascading dropdown)
- **Langkah**: tab Akun → sub Alamat → pilih provinsi → kota → kecamatan.
- **Expected**: dropdown kota muncul setelah provinsi dipilih, kecamatan muncul setelah kota. Save → tersimpan ke `pasien_profiles`.

---

## Modul 10 — Notifikasi & Forum

### TC-110 — Bell notification
- **Pre**: ada notif unread (status appt berubah, dll).
- **Langkah**: klik bell di topbar.
- **Expected**:
  - Dropdown muncul dengan list notif unread.
  - Badge merah dengan angka unread.
  - Klik notif → mark as read + redirect ke konteksnya.

### TC-111 — Tandai semua dibaca
- **Langkah**: di dropdown notif, klik "Tandai semua dibaca".
- **Expected**: badge hilang, semua notif berubah ke read state.

### TC-112 — Browse forum publik
- **Langkah**: buka `/forum` tanpa login.
- **Expected**: list pertanyaan publik bisa dilihat. Detail pertanyaan + jawaban dokter tampil.

### TC-113 — Buat pertanyaan tanpa login
- **Langkah**: di forum, klik "Ajukan Pertanyaan".
- **Expected**: redirect ke login dengan flash "Silakan login terlebih dahulu untuk mengajukan pertanyaan."

### TC-114 — Buat pertanyaan setelah login
- **Pre**: login.
- **Langkah**: forum → "Buat Pertanyaan" → isi judul + isi → submit.
- **Expected**: redirect ke detail pertanyaan, toast "Pertanyaan Anda berhasil diajukan! Dokter kami akan segera merespons."

---

## Modul 11 — Visual & Responsivitas

### TC-120 — Layout di mobile (375px)
- **Langkah**: DevTools → resize ke 375x812 (iPhone SE) → telusuri seluruh halaman.
- **Expected**:
  - Tidak ada horizontal scroll di halaman manapun.
  - Card produk/treatment 2 kolom (bukan 1).
  - Sidebar filter → collapse-able via tombol Filter.
  - Modal-modal centered, tidak tertimpa keyboard atau navbar.

### TC-121 — Tombol back-to-top di mobile
- **Pre**: scroll panel katalog (treatment/shop/bundle).
- **Langkah**: scroll ke bawah > 400px → tombol panah ↑ muncul.
- **Expected**: tombol **tidak overlap** dengan widget WhatsApp di pojok kanan bawah. Klik tombol → smooth scroll ke atas.

### TC-122 — Hero landing
- **Langkah**: buka landing di berbagai resolusi (1080p, 1440p, 4K, mobile).
- **Expected**:
  - Tinggi hero menyesuaikan viewport (minus navbar 80px), tidak menyisakan ruang kosong besar di bawah image.
  - Slider gambar ganti tiap 5 detik.
  - Tombol prev/next dan indicator slide bekerja.

### TC-123 — Show/hide password
- **Langkah**: di setiap form password (login, register, reset, confirm, ganti password, hapus akun, member register), klik ikon mata.
- **Expected**: tipe input toggle antara `password` dan `text`, ikon ganti antara eye dan eye-off, fokus tetap di input.

### TC-124 — WhatsApp widget
- **Langkah**: klik logo WhatsApp di pojok kanan bawah.
- **Expected**: card chat preview muncul, tombol "Mulai Chat" buka WA dengan template "Halo Aishi Beauty, saya ingin bertanya tentang layanan klinik."

---

## Modul 12 — Edge Case & Error Handling

### TC-130 — Akses route auth tanpa login
- **Langkah**: buka `/dashboard`, `/cart`, `/checkout`, `/appointment/booking-treatment`, dll tanpa login.
- **Expected**: redirect ke `/login`, setelah login dikembalikan ke URL itu (intended).

### TC-131 — CSRF expired
- **Langkah**: buka form, biarkan tab idle > 2 jam, submit.
- **Expected**: redirect dengan halaman 419 "Page Expired" atau auto-refresh + retry. Tidak crash 500.

### TC-132 — Karakter spesial di input
- **Langkah**: input nama "<script>alert(1)</script>", catatan dengan emoji 😀, search produk dengan kata 'O''Brien'.
- **Expected**: tidak ada XSS aktif (escape blade), data tersimpan apa adanya, tidak SQL error.

### TC-133 — Network offline saat submit
- **Langkah**: putuskan internet → submit form (booking, cart, profile).
- **Expected**: toast error "Tidak dapat terhubung ke server. Silakan periksa jaringan Anda.", state submit reset, tidak ada data setengah jadi.

### TC-134 — Akun di-disable admin
- **Pre**: minta developer/admin menyiapkan kondisi di mana akun testing dinonaktifkan dari panel admin. **Jangan** ubah status akun lewat database langsung.
- **Langkah**: dengan akun yang sudah dinonaktifkan, refresh halaman.
- **Expected**: auto logout + redirect login dengan pesan "Akun Anda dinonaktifkan."

### TC-135 — Bahasa Indonesia konsisten
- **Langkah**: buat error validasi (form kosong, email salah, dll) di seluruh form.
- **Expected**: pesan **selalu** dalam Bahasa Indonesia, tidak ada bahasa Inggris bocor (kecuali di label teknis seperti "Midtrans" / nama brand).

---

## Checklist Cross-Cutting (Wajib di setiap halaman)

- [ ] Tidak ada 404 / 500 / 419 saat normal flow.
- [ ] Console browser bersih (tidak ada error JS, missing asset, dll).
- [ ] Network tab: tidak ada request yang gagal selain yang disengaja (mis. polling).
- [ ] Loading state: setiap tombol submit menampilkan spinner saat request berjalan.
- [ ] Empty state: setiap list (cart, appointment, order, notif, forum, kategori) punya placeholder ramah dengan CTA.
- [ ] Toast: muncul di kanan atas, auto-dismiss 4 detik, tipe yang benar (success emerald / error rose / warning amber / info blue).
- [ ] Modal: semua punya tombol close (X / Escape / click-away), tidak ada modal yang stuck.
- [ ] Form validation: error muncul di bawah field, scroll otomatis ke field pertama yang error saat submit.
- [ ] Tanggal: format Indonesia "18 Mei 2026, 14:30 WIB". Mata uang "Rp 150.000".
- [ ] Status appointment: "Diajukan / Disetujui / Selesai / Batal / Tidak Hadir" (case konsisten, tidak ALL CAPS atau lowercase).

---

## Bug Reporting Template

Saat menemukan bug, isi format ini:

```
[BUG #001]
Test Case ID: TC-XX
Tanggal: 2026-05-22 14:30 WIB
QA: [nama]

Browser & Device: Chrome 120, MacBook Pro 14"
URL: https://aishibeauty.my.id/...
User Login: testuser@example.com

Pre-condition:
- ...

Langkah Reproduksi:
1. ...
2. ...
3. ...

Expected Result:
- ...

Actual Result:
- ...

Severity: Critical / High / Medium / Low
Screenshot: [link / attachment]
Console error: [log]
```

---

## Tips QA Junior

1. **Selalu test happy path dulu**, baru edge case.
2. **Pakai 2 akun terpisah** untuk test race condition (book slot bersamaan, kirim chat, dll). Minta developer siapkan akun, jangan bikin sendiri lewat DB.
3. **Bersihkan cache browser** setelah test besar supaya state tidak bocor antar test case (Ctrl+Shift+Del → cookies & site data domain aishibeauty.my.id).
4. **Jangan reset / seed ulang database**. Kalau butuh state spesifik untuk reproduksi, minta developer siapkan data via panel admin. Tugas QA hanya memverifikasi UI, bukan menyentuh data.
5. **Catat semua langkah**, jangan andalkan ingatan. Bug akan susah direproduksi tanpa catatan.
6. **Tes mobile** sungguh-sungguh di HP fisik kalau bisa, bukan cuma DevTools resize.
7. **Pertanyakan UX** yang terasa aneh — meski tidak crash, mungkin masih bug usability. Catat sebagai severity Low.
8. **Jangan klik "Hapus" sembarangan** di panel admin saat verifikasi. Kalau kepleset, segera lapor developer agar di-restore.
