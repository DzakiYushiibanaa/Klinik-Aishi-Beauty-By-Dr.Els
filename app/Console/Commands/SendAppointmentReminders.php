<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\FonnteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kirim pengingat WhatsApp H-1 untuk appointment besok.
 *
 * Dijadwalkan via Schedule::command(...) di routes/console.php.
 * Idempoten: tabel appointments tidak punya kolom flag reminder, jadi
 * pengiriman berulang dihindari dengan menjalankan command ini sekali
 * sehari (mis. 09:00 WIB) — risiko duplikat sangat kecil dan biaya
 * pesan Fonnte cukup ekonomis untuk diabaikan.
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Kirim pengingat WhatsApp untuk appointment yang dijadwalkan besok.';

    public function handle(FonnteService $fonnte): int
    {
        $tomorrowStart = now()->copy()->addDay()->startOfDay();
        $tomorrowEnd   = now()->copy()->addDay()->endOfDay();

        $appointments = Appointment::with([
                'items.treatment',
                'items.bundle',
                'items.consultation',
                'dokter',
                'terapis',
                'pasien.pasienProfile',
            ])
            ->whereIn('status', ['diajukan', 'disetujui'])
            ->whereBetween('tanggal_jam', [$tomorrowStart, $tomorrowEnd])
            ->get();

        $this->info("Menemukan {$appointments->count()} appointment besok.");

        $sent = 0;
        $skipped = 0;

        foreach ($appointments as $appointment) {
            try {
                $ok = $fonnte->sendAppointmentReminder($appointment);
                $ok ? $sent++ : $skipped++;
            } catch (\Throwable $e) {
                $skipped++;
                Log::warning('Reminder WA gagal: '.$e->getMessage(), [
                    'appointment_id' => $appointment->appointment_id,
                ]);
            }
        }

        $this->info("Reminder terkirim: {$sent}, skip: {$skipped}.");
        Log::info("Appointment reminders run: sent={$sent}, skipped={$skipped}");

        return self::SUCCESS;
    }
}
