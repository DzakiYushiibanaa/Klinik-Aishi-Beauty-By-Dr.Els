# Sequence Diagram Isi Formulir Pra-Konsultasi

```mermaid
sequenceDiagram
    actor Pasien
    participant Sistem as Sistem Laravel
    participant View as Halaman Pra-Konsultasi
    participant Validasi as Validasi Form
    participant Storage as Penyimpanan Foto
    participant DB as Database
    participant WA as Layanan WhatsApp

    Pasien->>Sistem: Membuka halaman pra-konsultasi
    Sistem->>Storage: Cek foto sementara milik pasien

    alt Foto sementara tersedia
        Storage-->>Sistem: Kirim URL foto sementara
        Sistem->>View: Tampilkan form dengan preview foto sebelumnya
    else Foto sementara tidak tersedia
        Storage-->>Sistem: Tidak ada foto sementara
        Sistem->>View: Tampilkan form kosong
    end

    View-->>Pasien: Menampilkan form multi-step pra-konsultasi
    Pasien->>View: Mengisi kondisi kulit, riwayat skincare, riwayat medis, dan unggah foto wajah
    Pasien->>View: Menekan tombol kirim
    View->>Pasien: Menampilkan modal konfirmasi

    alt Pasien membatalkan konfirmasi
        Pasien-->>View: Klik batal
        View-->>Pasien: Kembali ke form pra-konsultasi
    else Pasien menyetujui pengiriman
        Pasien->>Sistem: Submit formulir pra-konsultasi
        Sistem->>Storage: Simpan foto upload valid ke folder sementara
        Storage-->>Sistem: Foto sementara tersimpan
        Sistem->>Validasi: Validasi data formulir dan foto

        alt Validasi gagal
            Validasi-->>Sistem: Data atau foto tidak valid
            Sistem->>View: Kirim pesan error dan data lama
            View-->>Pasien: Tampilkan error pada step terkait
        else Validasi berhasil
            Validasi-->>Sistem: Data valid
            Sistem->>DB: Ambil data pasien berdasarkan user login
            DB-->>Sistem: Data pasien ditemukan

            Sistem->>Storage: Simpan atau pindahkan foto ke folder final pra-konsultasi
            Storage-->>Sistem: Path foto final tersedia

            Sistem->>DB: Simpan patient_forms dengan status "baru"
            DB-->>Sistem: Form pra-konsultasi berhasil disimpan

            Sistem->>Storage: Hapus session dan folder foto sementara
            Storage-->>Sistem: Foto sementara dibersihkan

            Sistem->>WA: Kirim notifikasi pra-konsultasi berhasil dikirim

            alt Notifikasi WhatsApp gagal
                WA-->>Sistem: Gagal mengirim notifikasi
                Sistem->>Sistem: Catat log, proses tetap berhasil
            else Notifikasi WhatsApp berhasil
                WA-->>Sistem: Notifikasi berhasil dikirim
            end

            Sistem-->>Pasien: Redirect ke dashboard tab Pra-Konsultasi dengan pesan sukses
        end
    end
```

Alur isi formulir pra-konsultasi dimulai saat pasien membuka halaman pra-konsultasi dan sistem menampilkan form multi-step beserta preview foto sementara jika sebelumnya sudah ada upload yang valid. Pasien mengisi data kondisi kulit, riwayat produk, riwayat medis, keluhan utama, serta mengunggah foto wajah. Setelah pasien mengonfirmasi pengiriman, sistem menyimpan foto valid ke penyimpanan sementara, melakukan validasi lengkap, lalu menyimpan data ke tabel `patient_forms` dengan status `baru`. Jika berhasil, foto dipindahkan ke folder final, data sementara dibersihkan, notifikasi WhatsApp dikirim secara best-effort, dan pasien diarahkan ke dashboard tab Pra-Konsultasi.
