<?php

namespace App\Filament\Resources\Appointments\Support;

use App\Models\Appointment;
use App\Models\JadwalPraktik;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;

class AppointmentSlotResolver
{
    public static function dateOptions(Get $get): array
    {
        $options = [];
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $jenis = $get('jenis');
        $dokterId = $get('dokter_id');

        for ($i = 0; $i < 14; $i++) {
            $date = now()->copy()->startOfDay()->addDays($i);

            if ($jenis === 'konsultasi') {
                $hari = $mapHari[$date->dayOfWeekIso] ?? null;
                $hasSchedule = $hari && $dokterId && JadwalPraktik::query()
                    ->where('user_id', $dokterId)
                    ->where('jenis_layanan', 'konsultasi')
                    ->whereRaw('LOWER(hari) = ?', [$hari])
                    ->where('is_active', true)
                    ->exists();

                if (!$hasSchedule) {
                    continue;
                }
            }

            $options[$date->toDateString()] = strtoupper($date->locale('id')->translatedFormat('D')) . ' ' . $date->format('d') . ' ' . $date->locale('id')->translatedFormat('M');
        }

        return $options;
    }

    public static function slotOptions(Get $get, ?string $date): array
    {
        if (!$date)
            return [];

        return $get('jenis') === 'treatment'
            ? self::treatmentSlotOptions($get, $date)
            : self::consultationSlotOptions($get, $date);
    }

    public static function slotDisabled(Get $get, ?string $date, string $slot): bool
    {
        if (!$date || !$slot)
            return true;

        $start = self::slotStart($date, $slot);
        if (!$start || $start->isPast())
            return true;

        if ($get('jenis') === 'treatment') {
            $booked = Appointment::query()
                ->where('jenis', 'treatment')
                ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                ->count();
            return $booked >= 2;
        }

        return Appointment::query()
            ->where('jenis', 'konsultasi')
            ->where('dokter_id', $get('dokter_id'))
            ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
            ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
            ->exists();
    }

    public static function treatmentSlotOptions(Get $get, string $date): array
    {
        $slots = ['08:00 - 10:00', '10:30 - 12:30', '13:00 - 15:00', '15:30 - 17:30'];
        $options = [];
        foreach ($slots as $slot) {
            $start = self::slotStart($date, $slot);
            $booked = $start
                ? Appointment::query()
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

    public static function consultationSlotOptions(Get $get, string $date): array
    {
        $dokterId = $get('dokter_id');
        if (!$dokterId)
            return [];

        $dt = Carbon::parse($date);
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $hari = $mapHari[$dt->dayOfWeekIso] ?? null;

        $jadwal = $hari ? JadwalPraktik::query()
            ->where('user_id', $dokterId)
            ->where('jenis_layanan', 'konsultasi')
            ->whereRaw('LOWER(hari) = ?', [$hari])
            ->where('is_active', true)
            ->first() : null;

        if (!$jadwal)
            return [];

        $start = Carbon::parse($date . ' ' . $jadwal->jam_mulai);
        $end = Carbon::parse($date . ' ' . $jadwal->jam_selesai);
        $duration = max(5, (int) ($jadwal->durasi_slot_menit ?: 60));
        $options = [];

        for ($cursor = $start->copy(); $cursor->lt($end); $cursor->addMinutes($duration)) {
            $value = $cursor->format('H:i');
            $isBooked = Appointment::query()
                ->where('jenis', 'konsultasi')
                ->where('dokter_id', $dokterId)
                ->where('tanggal_jam', $cursor->format('Y-m-d H:i:s'))
                ->whereIn('status', ['akan_datang', 'diproses', 'disetujui'])
                ->exists();

            $options[$value] = str_replace(':', '.', $value) . ' | ' . ($cursor->isPast() ? 'Lewat' : ($isBooked ? 'Terisi' : 'Tersedia'));
        }

        return $options;
    }

    public static function slotStart(string $date, string $slot): ?Carbon
    {
        $time = trim(explode('-', $slot)[0] ?? $slot);
        $time = str_replace('.', ':', $time);
        try {
            return Carbon::parse($date . ' ' . $time);
        } catch (\Throwable) {
            return null;
        }
    }
}
