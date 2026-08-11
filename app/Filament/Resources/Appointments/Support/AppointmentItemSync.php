<?php

namespace App\Filament\Resources\Appointments\Support;

use App\Models\Appointment;
use App\Models\Treatment;

class AppointmentItemSync
{
    /**
     * Transient store for the consultation_id captured during mutateFormDataUsing().
     * Keyed by record id (or 'new' for Create). Consumed from CreateAction->after()
     * / EditAction->after() via syncConsultationItem().
     *
     * We route the value through this store because Filament's `->after()` hook only
     * receives the dehydrated form data, and we need to strip consultation_id before
     * the appointments row is saved (the column does not exist on that table).
     */
    protected static array $pendingConsultation = [];

    /**
     * Pull consultation_id out of the given form data, stash it for the after() hook,
     * and return the cleaned data ready for the appointments table.
     */
    public static function captureAndStripConsultationId(array $data, string|int $key = 'new'): array
    {
        self::$pendingConsultation[(string) $key] = $data['consultation_id'] ?? null;
        unset($data['consultation_id']);
        return $data;
    }

    /**
     * Retrieve (and clear) the consultation_id previously captured for the given key.
     */
    public static function consumeConsultationId(string|int $key = 'new'): ?int
    {
        $key = (string) $key;
        $value = self::$pendingConsultation[$key] ?? null;
        unset(self::$pendingConsultation[$key]);
        return $value ? (int) $value : null;
    }

    /**
     * Sync the selected consultation_id into the appointment_items table.
     *
     * - Removes any previously attached consultation item for this appointment.
     * - Inserts a new item with item_type='consultation' when a consultation is selected
     *   and the appointment jenis is 'konsultasi'.
     *
     * Designed to be called from both CreateAction->after() and EditAction->after().
     */
    public static function syncConsultationItem(Appointment $record, $consultationId): void
    {
        // Remove any stale consultation items regardless of current jenis
        $record->items()->where('item_type', 'consultation')->delete();

        if ($record->jenis !== 'konsultasi' || !$consultationId) {
            return;
        }

        $record->items()->create([
            'item_type' => 'consultation',
            'consultation_id' => (int) $consultationId,
            'qty' => 1,
        ]);
    }

    /**
     * Resolve current unit price for a pending row in the "lanjut_items" repeater.
     *
     * @param  string|null  $type  'treatment' | 'bundle' | null
     */
    public static function resolveItemPrice(?string $type, $treatmentId): ?int
    {
        if ($type === 'treatment' && $treatmentId) {
            $harga = Treatment::query()->whereKey($treatmentId)->value('harga');
            return $harga !== null ? (int) $harga : null;
        }

        return null;
    }

    /**
     * Transient store for the follow-up treatment rows captured during mutateFormDataUsing().
     */
    protected static array $pendingFollowups = [];

    /**
     * Extract follow-up rows (lanjut_treatment / lanjut_items) from form data and stash them
     * so they can be persisted after the appointment record is saved.
     */
    public static function captureAndStripFollowupItems(array $data, string|int $key = 'new'): array
    {
        self::$pendingFollowups[(string) $key] = [
            'enabled' => (bool) ($data['lanjut_treatment'] ?? false),
            'items' => array_values(array_filter((array) ($data['lanjut_items'] ?? []), function ($row) {
                $type = $row['item_type'] ?? null;
                return ($type === 'treatment' && !empty($row['treatment_id']));
            })),
        ];

        unset($data['lanjut_treatment'], $data['lanjut_items']);

        return $data;
    }

    /**
     * Persist follow-up treatment/bundle items into appointment_items, replacing any
     * previous treatment/bundle rows for this appointment. Consultation items are preserved.
     */
    public static function syncFollowupItems(Appointment $record, string|int $key = 'new'): void
    {
        $key = (string) $key;
        $pending = self::$pendingFollowups[$key] ?? null;
        unset(self::$pendingFollowups[$key]);

        // Always clear previous follow-up rows first.
        $record->items()->whereIn('item_type', ['treatment'])->delete();

        if (!$pending || !$pending['enabled']) {
            return;
        }

        foreach ($pending['items'] as $row) {
            $type = $row['item_type'];
            $record->items()->create([
                'item_type' => $type,
                'treatment_id' => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'qty' => max(1, (int) ($row['qty'] ?? 1)),
                'handled_by_type' => $type === 'treatment' ? ($row['handled_by_type'] ?? null) : null,
                'handled_by_id' => $type === 'treatment' && !empty($row['handled_by_id'])
                    ? (int) $row['handled_by_id']
                    : null,
            ]);
        }
    }
}
