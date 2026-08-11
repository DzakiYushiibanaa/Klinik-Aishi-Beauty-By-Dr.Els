<?php

namespace App\Filament\Resources\Appointments\Tables\Concerns;

use App\Models\Appointment;
use App\Models\JadwalPraktik;
use App\Models\User;
use App\Notifications\AppointmentStatusChanged;
use App\Services\FonnteService;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

trait HasReschedule
{
    private static function rescheduleDateOptions(Appointment $record): array
    {
        $options = [];
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];

        for ($i = 0; $i < 14; $i++) {
            $date = now()->copy()->startOfDay()->addDays($i);

            if ($record->jenis !== 'treatment') {
                $hari = $mapHari[$date->dayOfWeekIso] ?? null;
                $hasSchedule = $hari && JadwalPraktik::query()
                    ->where('user_id', $record->dokter_id)
                    ->where('jenis_layanan', 'konsultasi')
                    ->whereRaw('LOWER(hari) = ?', [$hari])
                    ->where('is_active', true)
                    ->exists();

                if (! $hasSchedule) {
                    continue;
                }
            }

            $options[$date->toDateString()] = strtoupper($date->locale('id')->translatedFormat('D')) . ' ' . $date->format('d') . ' ' . $date->locale('id')->translatedFormat('M');
        }

        return $options;
    }

    private static function rescheduleSlotOptions(Appointment $record, ?string $date): array
    {
        if (! $date) {
            return [];
        }

        return $record->jenis === 'treatment'
            ? self::rescheduleTreatmentSlotOptions($record, $date)
            : self::rescheduleConsultationSlotOptions($record, $date);
    }

    private static function rescheduleSlotDisabled(Appointment $record, ?string $date, string $slot): bool
    {
        if (! $date || ! $slot) {
            return true;
        }

        $start = self::rescheduleSlotStart($date, $slot);
        if (! $start || $start->isPast()) {
            return true;
        }

        if ($record->jenis === 'treatment') {
            $booked = Appointment::query()
                ->whereKeyNot($record->appointment_id)
                ->where('jenis', 'treatment')
                ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                ->count();

            return $booked >= 2;
        }

        return Appointment::query()
            ->whereKeyNot($record->appointment_id)
            ->where('jenis', 'konsultasi')
            ->where('dokter_id', $record->dokter_id)
            ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
            ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
            ->exists();
    }

    private static function rescheduleTreatmentSlotOptions(Appointment $record, string $date): array
    {
        $slots = [
            '08:00 - 10:00',
            '10:30 - 12:30',
            '13:00 - 15:00',
            '15:30 - 17:30',
        ];

        $options = [];
        foreach ($slots as $slot) {
            $start = self::rescheduleSlotStart($date, $slot);
            $booked = $start
                ? Appointment::query()
                    ->whereKeyNot($record->appointment_id)
                    ->where('jenis', 'treatment')
                    ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                    ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                    ->count()
                : 2;

            $sisa = max(0, 2 - $booked);
            $label = str_replace(':', '.', $slot);
            $options[$slot] = $label . ' | ' . ($start?->isPast() ? 'Lewat' : ($sisa > 0 ? "Sisa {$sisa} slot" : 'Penuh'));
        }

        return $options;
    }

    private static function rescheduleConsultationSlotOptions(Appointment $record, string $date): array
    {
        if (! $record->dokter_id) {
            return [];
        }

        $dt = \Carbon\Carbon::parse($date);
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $hari = $mapHari[$dt->dayOfWeekIso] ?? null;

        $jadwal = $hari ? JadwalPraktik::query()
            ->where('user_id', $record->dokter_id)
            ->where('jenis_layanan', 'konsultasi')
            ->whereRaw('LOWER(hari) = ?', [$hari])
            ->where('is_active', true)
            ->first() : null;

        if (! $jadwal) {
            return [];
        }

        $start = \Carbon\Carbon::parse($date . ' ' . $jadwal->jam_mulai);
        $end = \Carbon\Carbon::parse($date . ' ' . $jadwal->jam_selesai);
        $duration = max(5, (int) ($jadwal->durasi_slot_menit ?: 60));
        $options = [];

        for ($cursor = $start->copy(); $cursor->lt($end); $cursor->addMinutes($duration)) {
            $value = $cursor->format('H:i');
            $isBooked = Appointment::query()
                ->whereKeyNot($record->appointment_id)
                ->where('jenis', 'konsultasi')
                ->where('dokter_id', $record->dokter_id)
                ->where('tanggal_jam', $cursor->format('Y-m-d H:i:s'))
                ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                ->exists();

            $options[$value] = str_replace(':', '.', $value) . ' | ' . ($cursor->isPast() ? 'Lewat' : ($isBooked ? 'Terisi' : 'Tersedia'));
        }

        return $options;
    }

    private static function rescheduleSlotStart(string $date, string $slot): ?\Carbon\Carbon
    {
        $time = trim(explode('-', $slot)[0] ?? $slot);
        $time = str_replace('.', ':', $time);

        try {
            return \Carbon\Carbon::parse($date . ' ' . $time);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function rescheduleConfirmationHtml(Appointment $record, ?string $date, ?string $slot): HtmlString
    {
        $old = $record->tanggal_jam
            ? $record->tanggal_jam->locale('id')->translatedFormat('l, d M Y') . ' Jam ' . $record->tanggal_jam->format('H:i') . ' WIB'
            : '-';

        $newStart = $date && $slot ? self::rescheduleSlotStart($date, $slot) : null;
        $new = $newStart
            ? $newStart->locale('id')->translatedFormat('l, d M Y') . ' Jam ' . $newStart->format('H:i') . ' WIB'
            : 'Pilih tanggal dan slot terlebih dahulu.';

        return new HtmlString(
            '<div style="display:grid; gap:16px;">'
            .'<div style="padding:16px; border:1px solid #E8E0D8; border-radius:12px; background:#FAF8F5;">'
            .'<div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Jadwal Lama</div>'
            .'<div style="font-weight:700; color:#111827;">'.e($old).'</div>'
            .'<div style="height:1px; background:#E8E0D8; margin:14px 0;"></div>'
            .'<div style="font-size:11px; font-weight:700; color:#CFAF53; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Jadwal Baru</div>'
            .'<div style="font-weight:800; color:#CFAF53;">'.e($new).'</div>'
            .'</div>'
            .'<div style="padding:12px 14px; border:1px solid #FDE68A; border-radius:12px; background:#FFFBEB; color:#92400E; font-size:13px; line-height:1.5;">'
            .'Perubahan jadwal dari admin akan langsung menyimpan appointment sebagai <strong>Disetujui</strong>.'
            .'</div>'
            .'</div>'
        );
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    private static function rescheduleAppointment(Appointment $record, array $data): array
    {
        $date = $data['new_tanggal'] ?? null;
        $slot = $data['new_jam'] ?? null;

        if (! $date || ! $slot) {
            return ['ok' => false, 'message' => 'Pilih tanggal dan slot baru terlebih dahulu.'];
        }

        if (self::rescheduleSlotDisabled($record, $date, $slot)) {
            return ['ok' => false, 'message' => 'Slot yang dipilih sudah tidak tersedia. Silakan pilih slot lain.'];
        }

        $newDateTime = self::rescheduleSlotStart($date, $slot);
        if (! $newDateTime) {
            return ['ok' => false, 'message' => 'Format jadwal baru tidak valid.'];
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $newDateTime) {
            $record->update([
                'tanggal_jam' => $newDateTime,
                'status' => 'disetujui',
                'cancel_reason' => null,
                'canceled_by' => null,
            ]);
        });

        $fresh = $record->fresh(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien']);

        if ($fresh?->pasien) {
            $fresh->pasien->notify(new AppointmentStatusChanged($fresh, 'Jadwal Diubah'));
        }

        try {
            if ($fresh) {
                app(FonnteService::class)->sendAppointmentRescheduled($fresh);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (admin ubah jadwal appointment): '.$e->getMessage());
        }

        return ['ok' => true];
    }

}
