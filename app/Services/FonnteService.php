<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service tipis untuk integrasi Fonnte WhatsApp API.
 *
 * Tujuan utama:
 *   1. Centralize seluruh logic HTTP ke Fonnte (tidak duplikat di controller).
 *   2. Normalisasi nomor HP Indonesia ke format 62XXXXXXXXX.
 *   3. Build template pesan untuk event-event utama klinik (booking,
 *      reschedule, cancel, payment, shipping, pra-konsultasi).
 *   4. Fail-safe: kalau API down / token kosong, log warning saja —
 *      tidak melempar exception ke alur bisnis utama.
 *
 * Pemakaian:
 *   app(FonnteService::class)->sendAppointmentCreated($appointment);
 *
 * Kalau butuh kirim teks bebas (mis. utility / testing):
 *   app(FonnteService::class)->send($phone, $message);
 */
class FonnteService
{
    /**
     * Kirim pesan WhatsApp generic ke nomor target.
     *
     * @param  string|null $phone   Nomor HP (boleh format 08…, +62…, 62…)
     * @param  string      $message Isi pesan plain text (boleh multi-line)
     * @return bool        true kalau request 2xx, false kalau gagal/skipped
     */
    public function send(?string $phone, string $message): bool
    {
        $target = $this->normalizePhone($phone);
        if ($target === null) {
            Log::warning('Fonnte: nomor HP tidak valid, skip kirim.', ['phone' => $phone]);
            return false;
        }

        $token = (string) config('services.fonnte.token');
        if ($token === '') {
            Log::warning('Fonnte: FONNTE_TOKEN belum di-set, skip kirim.', ['target' => $target]);
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->timeout(15)
                ->retry(2, 500)
                ->post(
                    rtrim((string) config('services.fonnte.base_url'), '/').'/send',
                    [
                        'target'      => $target,
                        'message'     => $message,
                        'countryCode' => '62',
                    ]
                );

            if ($response->successful()) {
                return true;
            }

            Log::warning('Fonnte: response non-2xx', [
                'target' => $target,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::warning('Fonnte: exception saat kirim WA', [
                'target' => $target,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Normalisasi nomor HP Indonesia → 62XXXXXXXXX.
     * Mengembalikan null kalau angka kurang dari 9 digit (invalid).
     */
    public function normalizePhone(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        // Buang semua karakter non-digit (spasi, +, -, (), dll).
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '' || $digits === null) {
            return null;
        }

        // 08xxxx → 628xxxx
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        // 8xxxx (tanpa 0) → 628xxxx
        elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }
        // sudah 62xxxx → biarkan
        elseif (!str_starts_with($digits, '62')) {
            // Bukan format Indonesia yang dikenal, tetap kirim apa adanya
            // tapi pastikan minimal panjang masuk akal di bawah.
        }

        // Minimal 10 digit (62 + 8 digit), realistis 11+.
        return strlen($digits) >= 10 ? $digits : null;
    }

    // ═══════════════════════════════════════════════════════════════
    // EVENT TEMPLATES (sumber kebenaran format pesan WhatsApp)
    // ═══════════════════════════════════════════════════════════════

    /**
     * 1. Appointment Created — kirim setelah pasien booking sukses.
     */
    public function sendAppointmentCreated(\App\Models\Appointment $appointment): bool
    {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Appointment berhasil dibuat ✨',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📋 Layanan:',
            $this->namaLayanan($appointment),
            '',
            '📅 Tanggal:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '👩‍⚕️ Dokter/Terapis:',
            $this->namaStaff($appointment),
            '',
            '📌 Status:',
            'Menunggu Kedatangan',
            '',
            'Mohon datang 10–15 menit lebih awal ya 😊',
            '',
            'Terima kasih telah memilih Aishi Beauty ✨',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 2. Appointment Reminder — kirim H-1 atau beberapa jam sebelum.
     *    Dipanggil oleh command Schedule (lihat console kernel).
     */
    public function sendAppointmentReminder(\App\Models\Appointment $appointment): bool
    {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Reminder Appointment ⏰',
            '',
            'Halo Kak '.$nama.' 👋',
            '',
            'Besok Anda memiliki jadwal appointment di Aishi Beauty ✨',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📋 Layanan:',
            $this->namaLayanan($appointment),
            '',
            '📅 Tanggal:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '👩‍⚕️ Dokter/Terapis:',
            $this->namaStaff($appointment),
            '',
            'Mohon datang tepat waktu 😊',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 2b. Appointment Approved — kirim setelah admin klik "Setujui"
     *     pada appointment yang masih berstatus "diajukan".
     */
    public function sendAppointmentApproved(\App\Models\Appointment $appointment): bool
    {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Janji temu Anda telah disetujui oleh admin ✅',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📋 Layanan:',
            $this->namaLayanan($appointment),
            '',
            '📅 Tanggal:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '👩‍⚕️ Dokter/Terapis:',
            $this->namaStaff($appointment),
            '',
            '📌 Status:',
            'Disetujui',
            '',
            'Mohon datang 10–15 menit lebih awal ya 😊',
            '',
            'Sampai bertemu di Aishi Beauty ✨',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 3. Appointment Rescheduled — kirim setelah pengajuan ubah jadwal.
     */
    public function sendAppointmentRescheduled(\App\Models\Appointment $appointment): bool
    {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Perubahan jadwal appointment berhasil dikonfirmasi ✨',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📅 Jadwal Baru:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam Baru:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '👩‍⚕️ Dokter/Terapis:',
            $this->namaStaff($appointment),
            '',
            'Sampai bertemu di Aishi Beauty 😊',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 4A. Appointment Cancelled by Clinic.
     */
    public function sendAppointmentCancelledByClinic(
        \App\Models\Appointment $appointment,
        ?string $alasan = null
    ): bool {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Mohon maaf, appointment berikut dibatalkan oleh pihak klinik.',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📋 Layanan:',
            $this->namaLayanan($appointment),
            '',
            '📅 Tanggal:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '📌 Alasan:',
            $alasan ?: ($appointment->cancel_reason ?: '-'),
            '',
            'Silakan melakukan booking ulang atau hubungi admin untuk bantuan lebih lanjut 🙏',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 4B. Appointment Cancelled by Patient.
     */
    public function sendAppointmentCancelledByPatient(\App\Models\Appointment $appointment): bool
    {
        [$phone, $nama] = $this->resolvePasienContact($appointment);
        if (!$phone) return false;

        $message = $this->tpl([
            'Pembatalan Appointment Berhasil ✅',
            '',
            'Halo Kak '.$nama.' 👋',
            '',
            'Appointment berikut telah berhasil dibatalkan.',
            '',
            '🆔 No. Appointment:',
            $this->kodeAppointment($appointment),
            '',
            '📋 Layanan:',
            $this->namaLayanan($appointment),
            '',
            '📅 Tanggal:',
            $this->formatTanggal($appointment->tanggal_jam),
            '',
            '⏰ Jam:',
            $this->formatJam($appointment->tanggal_jam),
            '',
            '📌 Status:',
            'Dibatalkan oleh Pasien',
            '',
            'Jika ingin melakukan appointment kembali, silakan booking ulang melalui website Aishi Beauty ✨',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 5. Payment Confirmed — kirim setelah order::status = sudah_dibayar.
     */
    public function sendPaymentConfirmed(\App\Models\Order $order): bool
    {
        $user  = $order->user;
        $phone = $this->bestPhoneFor($user);
        if (!$phone) return false;

        $nama   = $user?->name ?: 'Customer';
        $metode = \App\Support\PaymentMethodFormatter::format($order, ['empty_label' => '-']);
        $total  = number_format((int) ($order->grand_total ?? 0), 0, ',', '.');

        $message = $this->tpl([
            'Pembayaran Berhasil ✅',
            '',
            'Halo Kak '.$nama.' 👋',
            '',
            'Pembayaran berhasil dikonfirmasi.',
            '',
            '🧾 No. Invoice:',
            $order->kode_order ?? '-',
            '',
            '💳 Metode Pembayaran:',
            $metode,
            '',
            '💰 Total Pembayaran:',
            'Rp '.$total,
            '',
            '📌 Status:',
            'Lunas',
            '',
            'Terima kasih telah melakukan treatment di Aishi Beauty ✨',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 6. Product Order Shipped — kirim saat admin set resi & status dikirim.
     */
    public function sendOrderShipped(\App\Models\Order $order): bool
    {
        $user  = $order->user;
        $phone = $this->bestPhoneFor($user);
        if (!$phone) return false;

        $nama  = $user?->name ?: 'Customer';
        $kurir = $order->shipping_method
            ? strtoupper(str_replace('_', ' ', (string) $order->shipping_method))
            : '-';
        $resi  = $order->shipping_waybill ?: '-';

        $ringkasanProduk = $order->items
            ->take(3)
            ->map(fn ($i) => '- '.($i->product?->nama ?? $i->treatment?->nama ?? $i->description ?? 'Item').' x'.((int) $i->qty))
            ->implode("\n");

        if ($order->items->count() > 3) {
            $ringkasanProduk .= "\n+ ".($order->items->count() - 3).' produk lainnya';
        }
        if ($ringkasanProduk === '') {
            $ringkasanProduk = '-';
        }

        $message = $this->tpl([
            'Pesanan Sedang Dikirim 🚚',
            '',
            'Halo Kak '.$nama.' 👋',
            '',
            'Pesanan Anda telah dikirim.',
            '',
            '🛍️ No. Order:',
            $order->kode_order ?? '-',
            '',
            '📦 Produk:',
            $ringkasanProduk,
            '',
            '🚚 Kurir:',
            $kurir,
            '',
            '🧾 No. Resi:',
            $resi,
            '',
            'Terima kasih telah berbelanja di Aishi Beauty ✨',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 7. Pre-Consultation Submitted.
     */
    public function sendPraKonsulSubmitted(\App\Models\PatientForm $form): bool
    {
        $user  = $form->pasien?->user ?? $form->pasien;
        $phone = $this->bestPhoneFor($form->pasien?->user) ?: $this->bestPhoneFor($form->pasien);
        if (!$phone) return false;

        $nama = $user?->name ?: ($form->pasien?->nama ?: 'Customer');

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Form pra-konsultasi Anda berhasil dikirim ✨',
            '',
            '🆔 No. Pra-Konsultasi:',
            $this->kodePraKonsul($form),
            '',
            '📅 Tanggal Pengisian:',
            $this->formatTanggalSimple($form->created_at),
            '',
            '📌 Status:',
            'Menunggu Review Dokter',
            '',
            'Dokter akan segera meninjau informasi yang Kakak kirimkan.',
            '',
            'Terima kasih sudah mengisi form pra-konsultasi Aishi Beauty 😊',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 7b. Pre-Consultation Review Started — kirim saat dokter klik
     *     "Mulai Review". Pasien diberi tahu form sedang diperiksa.
     */
    public function sendPraKonsulReviewStarted(\App\Models\PatientForm $form): bool
    {
        $user  = $form->pasien?->user ?? $form->pasien;
        $phone = $this->bestPhoneFor($form->pasien?->user) ?: $this->bestPhoneFor($form->pasien);
        if (!$phone) return false;

        $nama       = $user?->name ?: ($form->pasien?->nama ?: 'Customer');
        $form->loadMissing('reviewer');
        $namaDokter = $form->reviewer?->name ?: '-';

        $waktuMulai = $form->review_started_at
            ? $this->formatTanggalSimple($form->review_started_at)
            : $this->formatTanggalSimple(now());

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Form pra-konsultasi Anda sedang ditinjau dokter ✨',
            '',
            '🆔 No. Pra-Konsultasi:',
            $this->kodePraKonsul($form),
            '',
            '👩‍⚕️ Ditinjau oleh:',
            $namaDokter,
            '',
            '🕒 Mulai Review:',
            $waktuMulai,
            '',
            '📌 Status:',
            'Sedang Direview',
            '',
            'Mohon ditunggu, dokter akan segera memberikan analisa dan rekomendasi treatment / skincare untuk Anda. Anda akan mendapatkan notifikasi lanjutan saat hasil review siap 😊',
        ]);

        return $this->send($phone, $message);
    }

    /**
     * 8. Pre-Consultation Reviewed (status = direkomendasikan / selesai).
     */
    public function sendPraKonsulReviewed(\App\Models\PatientForm $form): bool
    {
        $user  = $form->pasien?->user ?? $form->pasien;
        $phone = $this->bestPhoneFor($form->pasien?->user) ?: $this->bestPhoneFor($form->pasien);
        if (!$phone) return false;

        $nama       = $user?->name ?: ($form->pasien?->nama ?: 'Customer');

        // Prefer `reviewer` (dokter yang menekan "Mulai Review") karena
        // form pra-konsul yang dibuat dari dashboard pasien tidak punya
        // `dokter_id`. Fallback ke `dokter` untuk form lama / offline.
        $form->loadMissing(['reviewer', 'dokter']);
        $namaDokter = $form->reviewer?->name
            ?: ($form->dokter?->name ?: '-');

        // Ringkasan kondisi: prefer kolom `diagnosa` (diisi dokter saat
        // review). Fallback ke `keluhan_utama` (input pasien) supaya pesan
        // tidak kosong kalau dokter belum sempat menulis diagnosa.
        $ringkasan = trim((string) ($form->diagnosa ?: ($form->keluhan_utama ?: '-')));

        // Pisahkan rekomendasi item per kategori (treatment, produk).
        $form->loadMissing(['rekomendasiItems.treatment', 'rekomendasiItems.product']);

        $treatments = collect();
        $products   = collect();
        foreach (($form->rekomendasiItems ?? collect()) as $item) {
            if ($item->treatment_id && $item->treatment) {
                $treatments->push($item->treatment->nama);
            } elseif ($item->product_id && $item->product) {
                $products->push($item->product->nama);
            }
        }

        $message = $this->tpl([
            'Halo Kak '.$nama.' 👋',
            '',
            'Hasil pra-konsultasi Anda sudah ditinjau oleh dokter ✨',
            '',
            '🆔 No. Pra-Konsultasi:',
            $this->kodePraKonsul($form),
            '',
            '👩‍⚕️ Ditinjau oleh:',
            $namaDokter,
            '',
            '📋 Ringkasan Kondisi:',
            $ringkasan ?: 'Tidak ada keterangan tambahan dari dokter.',
            '',
            '💡 Rekomendasi Treatment:',
            $this->bulletList($treatments, 'Tidak ada rekomendasi treatment.'),
            '',
            '🧴 Rekomendasi Skincare:',
            $this->bulletList($products, 'Tidak ada rekomendasi skincare.'),
            '',
            'Jika ingin melanjutkan ke treatment/konsultasi, Kakak dapat melakukan booking melalui website 😊',
        ]);

        return $this->send($phone, $message);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS — formatting & resolver
    // ═══════════════════════════════════════════════════════════════

    /** Gabungkan baris-baris pesan menjadi single string siap kirim. */
    private function tpl(array $lines): string
    {
        return implode("\n", $lines);
    }

    /**
     * Render daftar nama menjadi list bullet WhatsApp:
     *
     *   • Item A
     *   • Item B
     *
     * Kalau collection kosong, return $emptyText apa adanya supaya
     * pesan tetap natural ("Tidak ada rekomendasi …") tanpa karakter "-".
     */
    private function bulletList(\Illuminate\Support\Collection|array $items, string $emptyText): string
    {
        $list = collect($items)
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values();

        if ($list->isEmpty()) {
            return $emptyText;
        }

        return $list->map(fn ($name) => '• '.$name)->implode("\n");
    }

    /** Coba ambil nomor HP terbaik dari user / pasien profile. */
    public function bestPhoneFor(?object $userOrPasien): ?string
    {
        if (!$userOrPasien) return null;

        $candidates = [
            $userOrPasien->phone        ?? null,
            $userOrPasien->no_telepon   ?? null,
            $userOrPasien->pasienProfile?->no_telepon ?? null,
            $userOrPasien->user?->phone ?? null,
        ];

        foreach ($candidates as $c) {
            $normalized = $this->normalizePhone($c);
            if ($normalized) {
                return $normalized;
            }
        }

        return null;
    }

    /** Resolve [phone, nama] dari Appointment. */
    private function resolvePasienContact(\App\Models\Appointment $appointment): array
    {
        $user = $appointment->pasien ?? $appointment->user;
        $nama = $user?->name ?: 'Customer';
        return [$this->bestPhoneFor($user), $nama];
    }

    private function kodeAppointment(\App\Models\Appointment $a): string
    {
        // TODO: Tambahkan kolom kode_appointment di tabel appointments
        // kalau klinik perlu kode publik. Sementara pakai ID padded.
        return 'APT-'.str_pad((string) $a->appointment_id, 6, '0', STR_PAD_LEFT);
    }

    private function kodePraKonsul(\App\Models\PatientForm $form): string
    {
        $id = $form->patient_form_id ?? $form->id ?? null;
        return $id ? 'PRK-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT) : '-';
    }

    private function namaLayanan(\App\Models\Appointment $a): string
    {
        $jenis = strtolower((string) ($a->jenis ?? ''));

        if ($jenis === 'konsultasi') {
            $item = $a->items?->first(fn ($i) => strtolower((string) $i->item_type) === 'consultation');
            return $item?->consultation?->nama ?: 'Konsultasi Dokter';
        }

        $items = $a->items?->filter(
            fn ($i) => in_array(strtolower((string) $i->item_type), ['treatment'], true)
        )?->values();

        if (!$items || $items->isEmpty()) {
            return ucfirst($jenis ?: 'Layanan');
        }

        $first = $items->first();
        $name = match (true) {
            strtolower((string) $first->item_type) === 'treatment' && $first->treatment => $first->treatment->nama,

            default => ucfirst($jenis),
        };

        $rest = $items->count() - 1;
        return $rest > 0 ? $name.' + '.$rest.' item' : $name;
    }

    private function namaStaff(\App\Models\Appointment $a): string
    {
        if (strtolower((string) $a->jenis) === 'konsultasi') {
            return $a->dokter?->name ?: '-';
        }
        return $a->terapis?->name ?: ($a->dokter?->name ?: '-');
    }

    private function formatTanggal($dt): string
    {
        if (!$dt) return '-';
        return \Illuminate\Support\Carbon::parse($dt)->locale('id')->translatedFormat('l, d F Y');
    }

    private function formatTanggalSimple($dt): string
    {
        if (!$dt) return '-';
        return \Illuminate\Support\Carbon::parse($dt)->locale('id')->translatedFormat('d F Y, H:i').' WIB';
    }

    private function formatJam($dt): string
    {
        if (!$dt) return '-';
        return \Illuminate\Support\Carbon::parse($dt)->format('H:i').' WIB';
    }
}
