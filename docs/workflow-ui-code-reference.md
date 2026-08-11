# Referensi Kode UI Alur Bisnis
Aplikasi Klinik "Aishi Beauty"

Dokumen ini berisi pemetaan alur bisnis utama beserta potongan kode dari sisi antarmuka pengguna (UI) yang paling representatif untuk setiap alur. Dokumentasi ini berfokus murni pada tampilan/interaksi (Blade view), bukan pada logika backend (Model/Controller/Filament Resource).

---

## 1. Booking Appointment
**Deskripsi alur:** Pasien mengajukan booking (konsultasi/treatment) secara mandiri melalui website. Pasien memilih layanan (treatment), jadwal, dan mengkonfirmasi pemesanan.

**File UI:** `resources/views/appointment/booking-treatment.blade.php`

```blade
<!-- Bagian pemilihan Treatment (Contoh interaksi klik Pilih Treatment) -->
<div class="px-4 pb-4">
    <button @click="toggleTreatment({{ $treatment->treatment_id }}, '{{ addslashes($treatment->nama) }}', {{ $treatment->harga }}, {{ $treatment->durasi_menit }}, '{{ $treatment->foto_path ? Storage::url($treatment->foto_path) : '' }}')"
        class="w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2"
        :class="isSelected({{ $treatment->treatment_id }}) ? 'bg-white border border-[#8C7A63] text-[#8C7A63]' : 'bg-[#3B3529] text-white hover:bg-[#2A261D]'">
        <template x-if="!isSelected({{ $treatment->treatment_id }})">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Pilih
            </span>
        </template>
        <template x-if="isSelected({{ $treatment->treatment_id }})">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Terpilih
            </span>
        </template>
    </button>
</div>
```
**Catatan:** Tampilan ini diatur sepenuhnya dengan menggunakan state Alpine.js (seperti `isSelected()`) untuk menangani alur multi-step booking di sisi client sebelum di-submit.

---

## 2. Pra-Konsultasi
**Deskripsi alur:** Pasien mengisi form pra-konsultasi yang berisi data kondisi wajah dan keluhan, yang nantinya akan ditinjau oleh dokter untuk memberikan rekomendasi awal.

**File UI:** `resources/views/pra/create.blade.php`

```blade
<!-- Form Navigasi Step & Konfirmasi Pengiriman -->
<div class="flex items-center justify-between px-6 sm:px-10 py-5 border-t border-gray-100 bg-[#FDFCFB]">
    <button type="button" x-show="step > 1" @click="prevStep()" x-cloak
        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </button>
    <div x-show="step === 1"></div>
    <button type="button" x-show="step < 5" @click="nextStep()"
        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
        Selanjutnya
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
    <button type="button" x-show="step === 5" @click="showModal = true" x-cloak
        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#333] transition-all">
        Kirim Form Sekarang
    </button>
</div>
```
**Catatan:** Form ini menggunakan pendekatan wizard/multi-step (`@include('pra.partials._step1')` dst) yang transisinya diatur oleh Alpine.js (`step`).

---

## 3. Konsultasi
**Deskripsi alur:** Pasien datang sesuai jadwal, dokter melakukan sesi konsultasi dan mencatat keluhan serta hasil diagnosis ke rekam medis.

**File UI:** ⚠️ Tidak ditemukan file UI terkait

**Catatan:** Proses pencatatan rekam medis dilakukan oleh dokter melalui panel admin (Filament Resource). Form input sepenuhnya di-*generate* secara dinamis dari kelas PHP Filament (misal: `RekamMedisResource.php`), sehingga tidak ada file `Blade`/frontend kustom untuk input rekam medis ini.

---

## 4. Pelaksanaan Treatment
**Deskripsi alur:** Pasien datang sesuai jadwal treatment, terapis melaksanakan treatment dan sistem mencatat komisi terapis setelah status appointment selesai.

**File UI:** ⚠️ Tidak ditemukan file UI terkait

**Catatan:** Sama seperti alur Konsultasi, eksekusi treatment dan update status diselesaikan melalui aksi (*Actions*) pada tabel atau form Filament di panel admin oleh akun staf/terapis. Tidak ada *custom frontend view* khusus untuk aksi pelaksanaan ini.

---

## 5. Pembayaran
**Deskripsi alur:** Pasien melakukan proses *checkout* dan pembayaran untuk order/appointment mereka. Jika sudah dibayar, sistem/admin memverifikasi dan status pesanan diperbarui.

**File UI:** `resources/views/checkout/payment.blade.php`

```blade
<!-- Tombol Konfirmasi Pembayaran/Checkout -->
<form action="{{ route('checkout.process') }}" method="POST">
    @csrf
    <!-- Input hidden data -->
    
    <button type="submit" 
        class="w-full flex items-center justify-center gap-2 bg-[#1A1A1A] hover:bg-[#333333] text-white py-4 rounded-xl font-bold text-lg transition-all duration-300 shadow-lg shadow-black/10 hover:shadow-black/20 group">
        <span>Lanjutkan ke Pembayaran</span>
        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
    </button>
</form>
```

---

## 6. Pembatalan Appointment
**Deskripsi alur:** Pasien dapat membatalkan janji temu (appointment) jika terdapat kendala. Alasan pembatalan disertakan saat proses pembatalan.

**File UI:** `resources/views/dashboard/_partials/appointments/_appointment_card.blade.php`

```blade
<!-- Modal Konfirmasi Batal Janji Temu -->
<div x-show="openCancelModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Overlay & Background -->
    <div class="relative bg-white rounded-3xl w-full max-w-md p-6 sm:p-8 shadow-2xl overflow-hidden text-center z-10" 
         @click.stop>
        
        <h3 class="text-[1.35rem] font-extrabold text-[#1A1A1A] tracking-tight leading-tight">Batalkan Janji?</h3>
        <p class="text-[0.95rem] text-slate-500 mt-1.5 leading-relaxed">Tindakan ini tidak dapat dibatalkan setelah dikonfirmasi.</p>
        
        <form action="{{ route('dashboard.appointments.cancel', $apt->appointment_id) }}" method="POST" class="mt-6">
            @csrf
            <!-- Select Alasan -->
            <select name="cancel_reason" required
                class="w-full text-left bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[0.95rem] text-slate-700 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                <option value="">-- Pilih Alasan Pembatalan --</option>
                <option value="Jadwal bentrok dengan acara lain">Jadwal bentrok dengan acara lain</option>
                <option value="Sakit / Kondisi kesehatan tidak memungkinkan">Sakit / Kondisi kesehatan tidak memungkinkan</option>
                <option value="Ingin mengubah treatment / layanan">Ingin mengubah treatment / layanan</option>
                <option value="Lainnya">Alasan lainnya</option>
            </select>
            
            <!-- Aksi Batal -->
            <div class="flex gap-3 mt-6">
                <button type="button" @click="openCancelModal = false" class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 text-[0.95rem] font-bold hover:bg-slate-50 transition-colors">Kembali</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-[0.95rem] font-bold cursor-pointer transition-colors">Ya, Batalkan</button>
            </div>
        </form>
    </div>
</div>
```

---

## 7. Forum Tanya Jawab
**Deskripsi alur:** Pasien mengajukan pertanyaan di halaman forum terkait permasalahan kulit atau produk, dan dokter (staf klinik) memberikan jawaban atau balasan.

**File UI:** `resources/views/forum/show.blade.php`

```blade
<!-- Bagian yang menampilkan pertanyaan beserta status dijawab/belum -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-center gap-4">
        <!-- Meta Info -->
        <div>
            <h3 class="font-bold text-slate-900">
                {{ $question->is_anonymous ? 'Penanya Anonim' : $question->user->name }}
            </h3>
            <p class="text-sm text-slate-500">
                Diposting {{ $question->created_at->diffForHumans() }}
            </p>
        </div>
    </div>

    <!-- Status Badge -->
    @php
        $statusLabel = 'Menunggu Jawaban';
        $statusColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';

        if ($question->status === 'ditutup') {
            $statusLabel = 'Selesai / Ditutup';
            $statusColor = 'bg-slate-100 text-slate-600 border-slate-200';
        } 
        elseif ($question->answers->count() > 0) {
            $statusLabel = 'Sudah Dijawab Dokter';
            $statusColor = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        }
    @endphp

    <span class="self-start rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider {{ $statusColor }}">
        {{ $statusLabel }}
    </span>
</div>
```

---

## Ringkasan

| Alur | File UI | Status |
| :--- | :--- | :---: |
| Booking Appointment | `resources/views/appointment/booking-treatment.blade.php` | ✅ Ditemukan |
| Pra-Konsultasi | `resources/views/pra/create.blade.php` | ✅ Ditemukan |
| Konsultasi | - | ⚠️ Tidak ditemukan |
| Pelaksanaan Treatment | - | ⚠️ Tidak ditemukan |
| Pembayaran | `resources/views/checkout/payment.blade.php` | ✅ Ditemukan |
| Pembatalan Appointment | `resources/views/dashboard/_partials/appointments/_appointment_card.blade.php` | ✅ Ditemukan |
| Forum Tanya Jawab | `resources/views/forum/show.blade.php` | ✅ Ditemukan |
