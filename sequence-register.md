# Sequence Diagram Register

```mermaid
sequenceDiagram
    actor Pasien
    participant Sistem as Sistem Laravel
    participant View as Halaman Register
    participant Validasi as Validasi Request
    participant DB as Database
    participant Role as Sistem Role
    participant Profil as Profil Pasien
    participant Sesi as Sesi Login

    Pasien->>Sistem: Membuka halaman register
    Sistem->>View: Menampilkan form registrasi
    View-->>Pasien: Form nama, email, password, konfirmasi password

    Pasien->>Sistem: Mengirim data registrasi
    Sistem->>Validasi: Validasi nama, email unik, password, konfirmasi password

    alt Validasi gagal
        Validasi-->>Sistem: Data tidak valid
        Sistem-->>View: Kirim pesan error validasi
        View-->>Pasien: Tampilkan error pada form register
    else Validasi berhasil
        Validasi-->>Sistem: Data valid
        Sistem->>DB: Mulai transaksi database
        Sistem->>DB: Simpan user baru dengan password terenkripsi
        DB-->>Sistem: User berhasil dibuat

        Sistem->>Role: Beri role "pasien" ke user
        Role->>DB: Simpan relasi user dengan role pasien
        DB-->>Role: Role berhasil disimpan

        Sistem->>Profil: Buat profil pasien otomatis
        Profil->>DB: Cek profil pasien berdasarkan user_id

        alt Profil pasien sudah ada
            DB-->>Profil: Profil ditemukan
            Profil-->>Sistem: Gunakan profil existing
        else Profil pasien belum ada
            Profil->>DB: Generate nomor rekam medis non-member (NM)
            Profil->>DB: Simpan data pasien default
            DB-->>Profil: Profil pasien berhasil dibuat
            Profil-->>Sistem: Profil pasien tersedia
        end

        Sistem->>DB: Commit transaksi database
        Sistem->>Sesi: Login otomatis user baru
        Sesi-->>Sistem: Session login aktif
        Sistem-->>Pasien: Redirect ke halaman landing
    end
```

Alur register dimulai saat pasien membuka halaman pendaftaran dan mengisi nama, email, password, serta konfirmasi password. Sistem melakukan validasi data, termasuk email harus unik dan password harus sesuai aturan. Jika validasi gagal, pasien dikembalikan ke form dengan pesan error. Jika berhasil, sistem membuat data user, memberikan role pasien, membuat profil pasien otomatis dengan nomor rekam medis non-member, lalu melakukan login otomatis dan mengarahkan pasien ke halaman landing.
