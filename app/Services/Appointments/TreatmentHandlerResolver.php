<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\Treatment;
use App\Models\User;

class TreatmentHandlerResolver
{
    /**
     * Hitung total estimasi tagihan dari array treatment_items
     * yang masih dalam state form (belum tersimpan ke DB).
     *
     * Treatment → harga_treatment × qty.
     */
    public static function calculateTreatmentSubtotal(array $items): int
    {
        if (empty($items)) {
            return 0;
        }

        $treatmentIds = collect($items)
            ->pluck('treatment_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $treatmentPrices = $treatmentIds
            ? Treatment::whereIn('treatment_id', $treatmentIds)->pluck('harga', 'treatment_id')->all()
            : [];

        $total = 0;
        foreach ($items as $row) {
            $type = $row['item_type'] ?? 'treatment';
            $qty  = max(1, (int) ($row['qty'] ?? 1));

            if ($type === 'treatment' && ! empty($row['treatment_id'])) {
                $price  = (int) ($treatmentPrices[$row['treatment_id']] ?? 0);
                $total += $price * $qty;
            }
        }

        return $total;
    }

    /**
     * Tipe staff yang boleh menangani treatment ini, ditentukan oleh
     * kategori-nya. Fallback ke ['dokter','terapis'] kalau treatment_id
     * belum dipilih.
     *
     * @return array<int, string>
     */
    public static function resolveAllowedHandlerTypes(?int $treatmentId): array
    {
        if (! $treatmentId) {
            return ['dokter', 'terapis'];
        }

        $treatment = Treatment::with('category')->find($treatmentId);
        $category  = $treatment?->category;

        return $category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];
    }

    /**
     * Daftar staff aktif untuk dropdown handler.
     *
     *   - dokter  → user role `dokter` dengan `dokter.status_praktik = aktif`
     *   - terapis → user role `terapis` dengan `terapis.status_kerja = aktif`
     *
     * Memastikan dokter/terapis yang nonaktif (cuti / resign / belum
     * di-assign profil) tidak ikut tampil di dropdown — menghindari
     * appointment ter-assign ke staff yang sudah tidak praktik.
     *
     * @return array<int, string>
     */
    public static function activeStaffOptions(string $type): array
    {
        $type = strtolower($type);

        if ($type === 'dokter') {
            return User::role('dokter')
                ->whereHas('dokterProfile', fn ($q) =>
                    $q->whereIn('status_praktik', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        if ($type === 'terapis') {
            return User::role('terapis')
                ->whereHas('terapisProfile', fn ($q) =>
                    $q->whereIn('status_kerja', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [];
    }

    public static function handlerOptionsForTreatment(?int $treatmentId): array
    {
        $allowed = self::resolveAllowedHandlerTypes($treatmentId);
        $options = [];

        if (in_array('dokter', $allowed, true)) {
            foreach (self::activeStaffOptions('dokter') as $id => $name) {
                $options[$id] = 'Dr. ' . $name . ' — Dokter';
            }
        }

        if (in_array('terapis', $allowed, true)) {
            foreach (self::activeStaffOptions('terapis') as $id => $name) {
                $options[$id] = $name . ' — Terapis';
            }
        }

        return $options;
    }

    public static function resolveDefaultHandlerIdForTreatment(?int $treatmentId, Appointment $appointment): ?int
    {
        $allowed = self::resolveAllowedHandlerTypes($treatmentId);

        if ($allowed === ['dokter']) {
            if ($appointment->dokter_id) {
                return (int) $appointment->dokter_id;
            }

            $user = auth()->user();

            return $user?->hasRole('dokter')
                ? (int) $user->id
                : null;
        }

        if ($allowed === ['terapis'] && $appointment->terapis_id) {
            return (int) $appointment->terapis_id;
        }

        [$defaultType, $defaultId] = self::resolveDefaultHandler($appointment);

        return $defaultType && in_array($defaultType, $allowed, true)
            ? $defaultId
            : null;
    }

    public static function resolveSelectedHandlerType(?int $userId): ?string
    {
        if (! $userId) return null;

        $user = User::find($userId);
        if (! $user) return null;

        if ($user->hasRole('dokter')) return 'dokter';
        if ($user->hasRole('terapis')) return 'terapis';

        return null;
    }

    public static function isDoctorOnlyTreatment(?int $treatmentId): bool
    {
        return self::resolveAllowedHandlerTypes($treatmentId) === ['dokter'];
    }

    public static function handlerHelperText(?int $treatmentId): ?string
    {
        return self::isDoctorOnlyTreatment($treatmentId)
            ? 'Treatment ini hanya dapat ditangani oleh dokter.'
            : null;
    }

    /**
     * Default handler dari appointment utama:
     *   - jenis konsultasi → dokter (kalau appointment.dokter_id ada)
     *   - jenis treatment  → prefer terapis, fallback ke dokter
     *
     * @return array{0: ?string, 1: ?int}
     */
    public static function resolveDefaultHandler(Appointment $appointment): array
    {
        if ($appointment->jenis === 'konsultasi' && $appointment->dokter_id) {
            return ['dokter', (int) $appointment->dokter_id];
        }

        if ($appointment->terapis_id) {
            return ['terapis', (int) $appointment->terapis_id];
        }
        if ($appointment->dokter_id) {
            return ['dokter', (int) $appointment->dokter_id];
        }

        return [null, null];
    }
}
