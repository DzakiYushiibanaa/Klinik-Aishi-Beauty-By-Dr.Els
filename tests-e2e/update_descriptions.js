const fs = require('fs');

const filePath = 'c:\\laragon\\www\\klinik_aishibeauty\\docs\\skenario_pasien_lengkap.md';
let content = fs.readFileSync(filePath, 'utf8');

// 3.1 Register
content = content.replace(
    /Halaman register akan menampilkan form pendaftaran akun baru dengan field: Nama Lengkap, Email, Password, dan Konfirmasi Kata Sandi\. Terdapat juga tombol "Buat Akun" dan opsi "Lanjut sebagai Tamu"\./,
    'Halaman register menampilkan formulir dengan field "NAMA LENGKAP", "ALAMAT EMAIL", "KATA SANDI", dan "KONFIRMASI SANDI". Terdapat tombol utama "Buat Akun", opsi "Daftar dengan Google", serta tautan "Masuk Disini" jika sudah memiliki akun.'
);

// 3.2 Login
content = content.replace(
    /Halaman ini menampilkan form untuk memasukkan Email dan Password pengguna yang sudah terdaftar\. Terdapat tombol "Masuk" untuk melakukan otentikasi, serta tombol "Masuk dengan Google"\./,
    'Halaman ini menampilkan form dengan judul "Selamat Datang". Terdapat field "EMAIL", "KATA SANDI", kotak centang "Ingat saya", serta tombol "Masuk Sekarang" dan "Masuk dengan Google".'
);

// 3.3 Dashboard
content = content.replace(
    /Halaman ini memuat ringkasan profil pasien, status pesanan terakhir, dan tombol akses cepat untuk "Buat Janji" atau "Pra Konsultasi"\./,
    'Halaman ini menampilkan ucapan "Selamat Malam, Pasien" dan menu navigasi kiri. Terdapat tombol akses cepat seperti "Buat Janji", "Konsultasi", "Treatment", "Belanja", serta kartu ringkasan untuk "Janji Temu Aktif", "Pesanan Aktif", dan "Kunjungan Selesai".'
);

// 3.4 Treatment
content = content.replace(
    /Pasien mengklik salah satu treatment untuk melihat informasi lebih lengkap mengenai manfaat, durasi, harga, dan syarat sebelum treatment\./,
    'Pasien mengklik salah satu treatment untuk melihat informasi spesifik seperti gambar treatment, harga per sesi, durasi, dan penjelasan "TENTANG PERAWATAN" beserta tombol "Buat Janji Temu".'
);

// 3.5 Booking Treatment (Fix Step 1 & 2 explanation since the image is still on Layanan)
content = content.replace(
    /Setelah treatment dipilih, pasien menentukan tanggal kedatangan dan slot jam yang masih tersedia oleh dokter\./,
    'Halaman akan menampilkan indikator tahapan (1. Layanan, 2. Jadwal, 3. Data Diri, 4. Pembayaran). Pada tahap ini pasien menelusuri katalog treatment yang akan di-booking.'
);

// 3.6 Booking Konsultasi
content = content.replace(
    /Pasien navigasi ke halaman Buat Janji Konsultasi \(\/appointment\/konsultasi\) dan memilih antara "Konsultasi Awal" atau "Konsultasi Lanjutan"\./,
    'Pasien diarahkan ke halaman dengan indikator tahapan Konsultasi Dokter. Pasien memilih jenis konsultasi, yaitu "Konsultasi Awal" (Rp 125.000) atau "Konsultasi Lanjutan" (Rp 100.000).'
);

// 3.7 Shop
content = content.replace(
    /Halaman katalog produk menampilkan daftar produk perawatan kulit \(skincare\) yang dijual secara e-commerce\. Pasien dapat menambahkan produk ke keranjang belanja \(Add to Cart\)\./,
    'Halaman "Katalog Produk" menampilkan daftar skincare dengan fitur pencarian dan filter di sebelah kiri. Terdapat kartu produk (misal: 24K AA+ Whitening Serum) beserta harganya.'
);
content = content.replace(
    /Setelah menekan tombol "Keranjang" atau Add to Cart pada produk, produk tersebut masuk ke dalam keranjang siap di-checkout\./,
    'Setelah menekan tombol tambah ke keranjang, sistem akan menampilkan pop-up konfirmasi "Berhasil Ditambahkan" yang berisi opsi "Lanjut Belanja" atau "Lihat Keranjang".'
);

// 3.9 Forum
content = content.replace(
    /Pasien memilih menu "Forum" untuk melihat daftar diskusi terbuka\./,
    'Halaman utama forum menampilkan judul "Tanya Dokter Kulit, Cepat & Terpercaya.", sebuah tombol "Buat Pertanyaan", serta kolom pencarian keluhan dan filter status.'
);

fs.writeFileSync(filePath, content);
console.log('Descriptions updated to match screenshots perfectly.');
