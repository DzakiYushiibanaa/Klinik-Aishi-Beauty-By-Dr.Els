# Sequence Diagram Mengisi Forum Pra-Konsultasi (Pasien)

```mermaid
sequenceDiagram
    actor Pasien
    participant Sistem as Sistem Laravel
    participant Forum as Halaman Forum
    participant Validasi as Validasi Pertanyaan
    participant DB as Database
    participant Dokter as Dokter

    Pasien->>Sistem: Membuka halaman Forum Konsultasi
    Sistem->>DB: Mengambil daftar pertanyaan forum
    DB-->>Sistem: Data pertanyaan dan jumlah jawaban
    Sistem->>Forum: Menampilkan daftar pertanyaan forum
    Forum-->>Pasien: Menampilkan forum, pencarian, filter status, dan tombol buat pertanyaan

    alt Pasien belum login
        Pasien->>Forum: Klik Buat Pertanyaan
        Forum->>Sistem: Meminta halaman buat pertanyaan
        Sistem-->>Pasien: Redirect ke halaman login
    else Pasien sudah login
        Pasien->>Forum: Klik Buat Pertanyaan
        Forum->>Sistem: Meminta halaman form pertanyaan
        Sistem->>Forum: Menampilkan form pertanyaan
        Forum-->>Pasien: Form judul, detail pertanyaan, dan opsi anonim

        Pasien->>Forum: Mengisi judul, detail pertanyaan, dan pilihan anonim
        Pasien->>Sistem: Mengirim pertanyaan forum
        Sistem->>Validasi: Validasi judul, detail pertanyaan, dan opsi anonim

        alt Validasi gagal
            Validasi-->>Sistem: Data tidak valid
            Sistem->>Forum: Mengirim pesan error dan data lama
            Forum-->>Pasien: Menampilkan error pada form pertanyaan
        else Validasi berhasil
            Validasi-->>Sistem: Data valid
            Sistem->>DB: Menyimpan pertanyaan forum dengan status "dibuka"
            DB-->>Sistem: Pertanyaan forum berhasil disimpan
            Sistem-->>Pasien: Redirect ke detail pertanyaan forum

            Pasien->>Sistem: Membuka detail pertanyaan
            Sistem->>DB: Mengambil pertanyaan dan jawaban dokter
            DB-->>Sistem: Data pertanyaan dan daftar jawaban
            Sistem->>Forum: Menampilkan detail pertanyaan
            Forum-->>Pasien: Menampilkan pertanyaan dan status jawaban

            alt Belum ada jawaban dokter
                Forum-->>Pasien: Menampilkan informasi belum ada jawaban dokter
            else Sudah ada jawaban dokter
                Dokter->>DB: Jawaban dokter sudah tersimpan pada forum
                DB-->>Sistem: Data jawaban dokter tersedia
                Forum-->>Pasien: Menampilkan jawaban dokter
            end
        end
    end

    opt Pasien mencari atau memfilter forum
        Pasien->>Forum: Mengisi kata kunci atau filter status
        Forum->>Sistem: Mengirim parameter pencarian/filter
        Sistem->>DB: Mencari pertanyaan berdasarkan judul, isi, atau status
        DB-->>Sistem: Hasil pencarian/filter
        Sistem->>Forum: Menampilkan daftar pertanyaan sesuai filter
        Forum-->>Pasien: Melihat hasil pencarian forum
    end
```

Alur mengisi forum pra-konsultasi dimulai saat pasien membuka halaman Forum Konsultasi dan sistem menampilkan daftar pertanyaan beserta jumlah jawaban dokter. Jika pasien belum login, sistem mengarahkan pasien ke halaman login ketika ingin membuat pertanyaan. Jika sudah login, pasien dapat membuka form, mengisi judul, detail pertanyaan, serta memilih apakah pertanyaan dikirim anonim. Sistem memvalidasi data, menyimpan pertanyaan ke tabel `forum_questions` dengan status `dibuka`, lalu mengarahkan pasien ke halaman detail pertanyaan. Pada halaman detail, pasien dapat melihat status pertanyaan dan jawaban dokter jika sudah tersedia.
