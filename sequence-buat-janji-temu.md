# Sequence Diagram Buat Janji Temu

```mermaid
sequenceDiagram
    actor Pasien
    participant Sistem as Sistem Laravel
    participant Halaman as Halaman Janji Temu
    participant Validasi as Validasi Data
    participant DB as Database
    participant Midtrans as Payment Gateway Midtrans
    participant WA as Layanan WhatsApp
    participant Admin as Admin/Frontdesk

    Pasien->>Sistem: Membuka menu Buat Janji Temu
    Sistem->>Halaman: Menampilkan pilihan layanan
    Halaman-->>Pasien: Pilihan Konsultasi Dokter atau Booking Treatment

    alt Profil pasien belum lengkap
        Pasien->>Sistem: Memilih layanan
        Sistem-->>Pasien: Redirect ke dashboard profil
        Sistem-->>Pasien: Menampilkan permintaan melengkapi data profil
    else Profil pasien lengkap
        alt Pasien memilih Konsultasi Dokter
            Pasien->>Sistem: Membuka form konsultasi
            Sistem->>DB: Mengambil daftar dokter aktif dan jadwal praktik
            DB-->>Sistem: Data dokter dan jadwal tersedia
            Sistem->>Halaman: Menampilkan wizard konsultasi
            Halaman-->>Pasien: Pilih jenis konsultasi, dokter, tanggal, dan jam

            Pasien->>Sistem: Mengirim data janji konsultasi
            Sistem->>Validasi: Validasi jenis konsultasi, dokter, tanggal, dan jam

            alt Validasi gagal
                Validasi-->>Sistem: Data tidak valid
                Sistem-->>Pasien: Menampilkan pesan error
            else Validasi berhasil
                Sistem->>DB: Cek slot dokter pada tanggal dan jam yang dipilih

                alt Slot konsultasi sudah terisi
                    DB-->>Sistem: Slot sudah digunakan pasien lain
                    Sistem-->>Pasien: Menampilkan pesan slot tidak tersedia
                else Slot konsultasi tersedia
                    DB-->>Sistem: Slot tersedia
                    Sistem->>DB: Menyimpan appointment konsultasi dengan status "diajukan"
                    Sistem->>DB: Menyimpan appointment item konsultasi awal/lanjutan
                    DB-->>Sistem: Janji konsultasi berhasil dibuat

                    Sistem->>WA: Mengirim notifikasi janji temu dibuat

                    alt WhatsApp gagal terkirim
                        WA-->>Sistem: Gagal mengirim notifikasi
                        Sistem->>Sistem: Catat log, proses tetap berhasil
                    else WhatsApp berhasil terkirim
                        WA-->>Sistem: Notifikasi berhasil dikirim
                    end

                    Sistem-->>Pasien: Mengirim respons sukses
                    Sistem-->>Pasien: Janji temu menunggu konfirmasi admin
                    Admin-->>Pasien: Konfirmasi dilakukan melalui panel admin pada proses terpisah
                end
            end

        else Pasien memilih Booking Treatment
            Pasien->>Sistem: Membuka form booking treatment
            Sistem->>DB: Mengambil treatment aktif dan kategori treatment
            DB-->>Sistem: Data treatment tersedia
            Sistem->>Halaman: Menampilkan wizard booking treatment
            Halaman-->>Pasien: Pilih treatment, tanggal, dan slot treatment

            Pasien->>Sistem: Mengecek ketersediaan slot treatment
            Sistem->>DB: Hitung jumlah appointment treatment pada slot
            DB-->>Sistem: Data kapasitas slot
            Sistem-->>Pasien: Menampilkan sisa slot

            Pasien->>Sistem: Mengirim booking treatment
            Sistem->>Validasi: Validasi treatment, tanggal, dan jam

            alt Validasi gagal
                Validasi-->>Sistem: Data tidak valid
                Sistem-->>Pasien: Menampilkan pesan error
            else Validasi berhasil
                Sistem->>DB: Cek ulang kapasitas slot treatment

                alt Slot treatment penuh
                    DB-->>Sistem: Slot sudah penuh
                    Sistem-->>Pasien: Menampilkan pesan slot penuh
                else Slot treatment tersedia
                    DB-->>Sistem: Slot tersedia
                    Sistem->>DB: Menyimpan appointment treatment dengan status "menunggu_pembayaran"
                    Sistem->>DB: Menyimpan appointment item treatment
                    Sistem->>DB: Membuat order dan order item treatment
                    DB-->>Sistem: Booking dan order berhasil tersimpan

                    Sistem->>Midtrans: Meminta Snap Token pembayaran

                    alt Payment Gateway gagal
                        Midtrans-->>Sistem: Snap Token gagal dibuat
                        Sistem-->>Pasien: Booking tersimpan, pembayaran perlu dibantu klinik
                    else Payment Gateway berhasil
                        Midtrans-->>Sistem: Snap Token diterima
                        Sistem->>DB: Menyimpan Snap Token pada order
                        DB-->>Sistem: Snap Token tersimpan
                        Sistem-->>Pasien: Mengirim Snap Token pembayaran
                    end
                end
            end
        end
    end
```

Alur buat janji temu dimulai saat pasien membuka menu janji temu dan memilih jenis layanan. Jika profil belum lengkap, sistem mengarahkan pasien untuk melengkapi data terlebih dahulu. Pada jalur konsultasi, pasien memilih jenis konsultasi, dokter, tanggal, dan jam; sistem memvalidasi data, memeriksa ketersediaan slot dokter, lalu menyimpan appointment dengan status `diajukan` beserta item konsultasi. Pada jalur treatment, pasien memilih treatment dan slot, sistem memeriksa kapasitas maksimal slot, membuat appointment berstatus `menunggu_pembayaran`, membuat order serta order item, lalu meminta Snap Token ke Midtrans. Jika payment gateway gagal, booking tetap tersimpan tetapi pembayaran perlu dibantu pihak klinik.
