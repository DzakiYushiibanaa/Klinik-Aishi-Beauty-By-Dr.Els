<?php

namespace App\Filament\Resources\Appointments\Tables\Concerns;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusChanged;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

trait HasStaffAssignment
{
    private static function canManageStaff(): bool
    {
        return Filament::auth()->user()?->hasAnyRole(['admin', 'frontdesk']) ?? false;
    }

    private static function staffAssignmentSchema(Appointment $record): array
    {
        return [
            Placeholder::make('appointment_summary')
                ->hiddenLabel()
                ->content(function () use ($record): HtmlString {
                    $appointment = $record->loadMissing(['pasien', 'items.treatment', 'items.consultation']);
                    $patient = $appointment->pasien?->name ?? '-';
                    $schedule = $appointment->tanggal_jam
                        ? $appointment->tanggal_jam->format('d M Y H:i')
                        : '-';

                    return new HtmlString(
                        '<div style="display:grid; gap:6px; padding:12px 14px; border:1px solid #E5E7EB; border-radius:8px; background:#F9FAFB;">'
                        .'<div style="font-weight:700; color:#111827;">'.e($patient).'</div>'
                        .'<div style="font-size:0.875rem; color:#4B5563;">Tanggal & jam: '.e($schedule).'</div>'
                        .'</div>'
                    );
                }),

            Repeater::make('service_items')
                ->label('Daftar Treatment / Konsultasi')
                ->schema([
                    Hidden::make('appointment_item_id'),
                    Hidden::make('item_type'),
                    Hidden::make('treatment_id'),
                    Hidden::make('consultation_id'),
                    Hidden::make('service_label')
                        ->dehydrated(false),
                    Hidden::make('handled_by_type')
                        ->dehydrated(),

                    Grid::make(['default' => 1, 'md' => 3])->schema([
                        Placeholder::make('service_name')
                            ->label('Layanan')
                            ->content(fn (Get $get): string => (string) ($get('service_label') ?? '-')),

                        Select::make('handled_by_id')
                            ->label('Dokter / Terapis')
                            ->placeholder(fn (Get $get): string => self::isDoctorOnlyAssignment($get)
                                ? 'Pilih dokter'
                                : 'Pilih petugas')
                            ->options(fn (Get $get): array => self::staffOptionsForAssignment($get))
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Pilih dokter/terapis untuk semua layanan.',
                            ])
                            ->afterStateUpdated(fn ($state, $set) => $set('handled_by_type', $state ? self::resolveSelectedHandlerType((int) $state) : null))
                            ->dehydrated()
                            ->columnSpan(['md' => 2]),
                    ]),
                ])
                ->reorderable(false)
                ->addable(false)
                ->deletable(false)
                ->columnSpanFull(),
        ];
    }

    private static function staffAssignmentRows(Appointment $record): array
    {
        $record->loadMissing(['items.treatment.category', 'items.consultation']);

        return $record->items
            ->filter(fn ($item): bool => in_array($item->item_type, ['consultation', 'treatment'], true))
            ->map(function ($item) use ($record): array {
                $label = match ($item->item_type) {
                    'consultation' => $item->consultation?->nama ?? 'Konsultasi',
                    'treatment' => $item->treatment?->nama ?? 'Treatment',
                    default => 'Layanan',
                };

                $handledByType = $item->handled_by_type;
                $handledById = $item->handled_by_id;

                if ($item->item_type === 'consultation' && empty($handledById) && $record->dokter_id) {
                    $handledByType = 'dokter';
                    $handledById = $record->dokter_id;
                }

                return [
                    'appointment_item_id' => $item->appointment_item_id,
                    'item_type' => $item->item_type,
                    'treatment_id' => $item->treatment_id,
                    'consultation_id' => $item->consultation_id,
                    'service_label' => $label,
                    'handled_by_type' => $handledByType,
                    'handled_by_id' => $handledById,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    private static function saveStaffAssignment(Appointment $record, array $data): array
    {
        $rows = array_values((array) ($data['service_items'] ?? []));

        if ($rows === []) {
            return ['ok' => false, 'message' => 'Tidak ada layanan yang perlu diatur staff-nya.'];
        }

        $doctorId = null;
        $therapistId = null;

        foreach ($rows as $row) {
            if (empty($row['appointment_item_id']) || empty($row['handled_by_id'])) {
                return ['ok' => false, 'message' => 'Pilih dokter/terapis untuk semua layanan.'];
            }

            $handlerType = self::resolveSelectedHandlerType((int) $row['handled_by_id']);
            if (! $handlerType) {
                return ['ok' => false, 'message' => 'Pilih dokter/terapis untuk semua layanan.'];
            }

            $allowed = self::allowedHandlerTypesForAssignment($row);
            if (! in_array($handlerType, $allowed, true)) {
                return [
                    'ok' => false,
                    'message' => $allowed === ['dokter']
                        ? 'Treatment ini hanya dapat ditangani oleh dokter.'
                        : 'Pilih dokter/terapis untuk semua layanan.',
                ];
            }

            $record->items()
                ->whereKey((int) $row['appointment_item_id'])
                ->update([
                    'handled_by_type' => $handlerType,
                    'handled_by_id' => (int) $row['handled_by_id'],
                ]);

            if ($handlerType === 'dokter' && ! $doctorId) {
                $doctorId = (int) $row['handled_by_id'];
            }
            if ($handlerType === 'terapis' && ! $therapistId) {
                $therapistId = (int) $row['handled_by_id'];
            }
        }

        $record->update([
            'status' => 'disetujui',
            'dokter_id' => $doctorId,
            'terapis_id' => $therapistId,
        ]);

        return ['ok' => true];
    }

    private static function staffOptionsForAssignment(Get $get): array
    {
        $options = [];
        $allowed = self::allowedHandlerTypesForAssignment([
            'item_type' => $get('item_type'),
            'treatment_id' => $get('treatment_id'),
        ]);

        if (in_array('dokter', $allowed, true)) {
            foreach (self::activeStaffOptions('dokter') as $id => $name) {
                $options[$id] = 'Dr. '.$name.' — Dokter';
            }
        }

        if (in_array('terapis', $allowed, true)) {
            foreach (self::activeStaffOptions('terapis') as $id => $name) {
                $options[$id] = $name.' — Terapis';
            }
        }

        return $options;
    }

    private static function allowedHandlerTypesForAssignment(array $row): array
    {
        if (($row['item_type'] ?? null) === 'consultation') {
            return ['dokter'];
        }

        if (($row['item_type'] ?? null) !== 'treatment' || empty($row['treatment_id'])) {
            return [];
        }

        $treatment = \App\Models\Treatment::with('category')->find($row['treatment_id']);

        return $treatment?->category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];
    }

    private static function activeStaffOptions(string $type): array
    {
        if ($type === 'dokter') {
            return User::role('dokter')
                ->whereHas('dokterProfile', fn (Builder $query) =>
                    $query->whereIn('status_praktik', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        if ($type === 'terapis') {
            return User::role('terapis')
                ->whereHas('terapisProfile', fn (Builder $query) =>
                    $query->whereIn('status_kerja', ['aktif', 'Aktif'])
                )
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [];
    }

    private static function resolveSelectedHandlerType(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);
        if (! $user) {
            return null;
        }

        if ($user->hasRole('dokter')) {
            return 'dokter';
        }

        if ($user->hasRole('terapis')) {
            return 'terapis';
        }

        return null;
    }

    private static function isDoctorOnlyAssignment(Get $get): bool
    {
        return self::allowedHandlerTypesForAssignment([
            'item_type' => $get('item_type'),
            'treatment_id' => $get('treatment_id'),
        ]) === ['dokter'];
    }

    private static function userAssignedToAppointment(Appointment $record): bool
    {
        $user = Filament::auth()->user();
        if (! $user) {
            return false;
        }

        $type = $user->hasRole('dokter')
            ? 'dokter'
            : ($user->hasRole('terapis') ? 'terapis' : null);

        if (! $type) {
            return false;
        }

        if ($type === 'dokter' && (int) $record->dokter_id === (int) $user->getKey()) {
            return true;
        }

        if ($type === 'terapis' && (int) $record->terapis_id === (int) $user->getKey()) {
            return true;
        }

        return $record->items()
            ->where('handled_by_type', $type)
            ->where('handled_by_id', $user->getKey())
            ->exists();
    }
}
