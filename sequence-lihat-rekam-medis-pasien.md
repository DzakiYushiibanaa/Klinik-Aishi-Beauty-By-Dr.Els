# Sequence Diagram Lihat Rekam Medis dari Sisi Pasien

```mermaid
sequenceDiagram
    actor Pasien
    participant Sistem as Sistem Laravel
    participant Dashboard as Dashboard Pasien
    participant DB as Database
    participant Storage as Penyimpanan Foto

    Pasien->>Sistem: Login dan membuka dashboard
    Sistem->>DB: Mengambil data user dan profil pasien
    DB-->>Sistem: Data pasien ditemukan

    Sistem->>DB: Mengambil daftar rekam medis milik pasien login
    DB-->>Sistem: Data rekam medis, appointment, dokter, terapis, dan item layanan
    Sistem->>Dashboard: Mengirim data dashboard dan rekam medis
    Dashboard-->>Pasien: Menampilkan menu dashboard pasien

    Pasien->>Dashboard: Membuka tab Rekam Medis

    alt Rekam medis belum tersedia
        Dashboard-->>Pasien: Menampilkan pesan "Belum ada rekam medis"
    else Rekam medis tersedia
        Dashboard-->>Pasien: Menampilkan daftar rekam medis pasien
        Dashboard-->>Pasien: Menampilkan nomor kunjungan, tanggal, dokter/terapis, dan layanan

        opt Pasien mencari rekam medis
            Pasien->>Dashboard: Memasukkan kata kunci diagnosa atau layanan
            Dashboard->>Dashboard: Memfilter daftar rekam medis pada tampilan

            alt Data sesuai pencarian tidak ada
                Dashboard-->>Pasien: Daftar kosong sesuai kata kunci pencarian
            else Data sesuai pencarian ditemukan
                Dashboard-->>Pasien: Menampilkan rekam medis yang sesuai pencarian
            end
        end

        Pasien->>Dashboard: Klik Lihat Detail pada salah satu rekam medis
        Dashboard-->>Pasien: Menampilkan detail hasil pemeriksaan
        Dashboard-->>Pasien: Menampilkan keluhan, diagnosa, tindakan, produk rekomendasi, dan catatan lanjutan

        alt Rekam medis memiliki foto dokumentasi
            Dashboard->>Storage: Mengambil URL foto sebelum/sesudah
            Storage-->>Dashboard: URL foto tersedia
            Dashboard-->>Pasien: Menampilkan foto dokumentasi

            opt Pasien membuka foto
                Pasien->>Dashboard: Klik foto dokumentasi
                Dashboard-->>Pasien: Menampilkan foto dalam lightbox
            end
        else Tidak ada foto dokumentasi
            Dashboard-->>Pasien: Detail tampil tanpa dokumentasi foto
        end
    end
```

Alur lihat rekam medis dari sisi pasien dimulai ketika pasien membuka dashboard. Sistem mengambil data pasien login dan daftar rekam medis yang terkait dengan `pasien_id` milik user tersebut, termasuk relasi appointment, dokter, terapis, dan layanan yang dilakukan. Jika belum ada rekam medis, dashboard menampilkan pesan kosong. Jika data tersedia, pasien dapat melihat daftar rekam medis, mencari berdasarkan diagnosa atau layanan, membuka detail hasil pemeriksaan, melihat keluhan, diagnosa, tindakan, produk rekomendasi, catatan lanjutan, serta membuka dokumentasi foto sebelum/sesudah jika tersedia.
