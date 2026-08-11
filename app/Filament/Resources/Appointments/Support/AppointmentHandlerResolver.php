<?php

namespace App\Filament\Resources\Appointments\Support;

use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AppointmentHandlerResolver
{
    public static function resolveAllowedHandlerTypes(?int $treatmentId): array
    {
        if (!$treatmentId) {
            return ['dokter', 'terapis'];
        }

        $treatment = Treatment::with('category')->find($treatmentId);

        return $treatment?->category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];
    }

    public static function handlerOptionsForTreatment(?int $treatmentId): array
    {
        $allowed = self::resolveAllowedHandlerTypes($treatmentId);
        $options = [];

        if (in_array('dokter', $allowed, true)) {
            foreach (self::activeStaffOptions('dokter') as $id => $name) {
                $options[$id] = 'Dr. ' . $name . ' - Dokter';
            }
        }

        if (in_array('terapis', $allowed, true)) {
            foreach (self::activeStaffOptions('terapis') as $id => $name) {
                $options[$id] = $name . ' - Terapis';
            }
        }

        return $options;
    }

    public static function activeStaffOptions(string $type): array
    {
        if ($type === 'dokter') {
            return User::role('dokter')
                ->whereHas(
                    'dokterProfile',
                    fn(Builder $query) =>
                    $query->whereIn('status_praktik', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        if ($type === 'terapis') {
            return User::role('terapis')
                ->whereHas(
                    'terapisProfile',
                    fn(Builder $query) =>
                    $query->whereIn('status_kerja', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [];
    }

    public static function defaultHandlerIdForTreatment(?int $treatmentId): ?int
    {
        $allowed = self::resolveAllowedHandlerTypes($treatmentId);

        return $allowed === ['dokter']
            ? array_key_first(self::activeStaffOptions('dokter'))
            : null;
    }

    public static function resolveSelectedHandlerType(?int $userId): ?string
    {
        if (!$userId)
            return null;

        $user = User::find($userId);
        if (!$user)
            return null;

        if ($user->hasRole('dokter'))
            return 'dokter';
        if ($user->hasRole('terapis'))
            return 'terapis';

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
}
