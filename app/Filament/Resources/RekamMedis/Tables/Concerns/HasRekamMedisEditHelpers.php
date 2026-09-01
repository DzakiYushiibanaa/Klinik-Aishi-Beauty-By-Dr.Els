<?php

namespace App\Filament\Resources\RekamMedis\Tables\Concerns;

use App\Models\Product;
use App\Models\RekamMedis;
use App\Models\Treatment;

trait HasRekamMedisEditHelpers
{
    private static function hydrateEditForm(RekamMedis $record): array
    {
        $tindakanItems = [];
        $appointment = $record->appointment;

        if ($appointment) {
            $tindakanItems = $appointment->items()
                ->whereIn('item_type', ['treatment'])
                ->get()
                ->map(fn ($i) => [
                    'item_type'    => $i->item_type,
                    'treatment_id' => $i->treatment_id,
                    'qty'          => (int) ($i->qty ?? 1),
                ])
                ->all();
        }

        // Best-effort: parse "produk_rekomendasi" text -> rows by matching product names.
        $regimenItems = [];
        $rawRegimen = (string) ($record->produk_rekomendasi ?? '');
        if ($rawRegimen !== '') {
            foreach (preg_split('/\s*,\s*/', $rawRegimen) as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') continue;

                // Extract qty if format "Name xN"
                $qty = 1;
                if (preg_match('/^(.*?)\s*x\s*(\d+)\s*$/i', $chunk, $m)) {
                    $name = trim($m[1]);
                    $qty  = max(1, (int) $m[2]);
                } else {
                    $name = $chunk;
                }

                $product = Product::query()
                    ->where('nama', $name)
                    ->orWhere('nama', 'like', $name . '%')
                    ->first();

                if ($product) {
                    $regimenItems[] = [
                        'product_id' => $product->product_id,
                        'qty'        => $qty,
                    ];
                }
            }
        }

        return [
            'keluhan'             => $record->keluhan,
            'diagnosa'            => $record->diagnosa,
            'catatan_lanjutan'    => $record->catatan_lanjutan,
            'foto_sebelum_path'   => $record->foto_sebelum_path,
            'foto_sesudah_path'   => $record->foto_sesudah_path,
            'tindakan_items'      => $tindakanItems,
            'regimen_items'       => $regimenItems,
        ];
    }

    /**
     * Transform form data into what the rekam_medis table expects.
     * - tindakan (text)              ← rendered from tindakan_items rows
     * - produk_rekomendasi (text)    ← rendered from regimen_items rows
     * The raw tindakan_items array is stashed under `_tindakan_items_cache`
     * so ->after() can sync it into appointment_items.
     */
    private static function prepareSaveData(array $data, RekamMedis $record): array
    {
        // Render tindakan text
        $tindakanRows = array_values(array_filter((array) ($data['tindakan_items'] ?? []), function ($row) {
            $type = $row['item_type'] ?? null;
            return ($type === 'treatment' && ! empty($row['treatment_id']));
        }));

        $data['tindakan'] = self::renderTindakanText($tindakanRows);

        // Render regimen text
        $regimenRows = array_values(array_filter((array) ($data['regimen_items'] ?? []), function ($row) {
            return ! empty($row['product_id']);
        }));

        $data['produk_rekomendasi'] = self::renderRegimenText($regimenRows);

        // Stash raw rows for the ->after() hook (they're not real columns).
        $data['_tindakan_items_cache'] = $tindakanRows;

        // Remove non-column keys so Eloquent doesn't complain.
        unset($data['tindakan_items'], $data['regimen_items']);

        return $data;
    }

    private static function renderTindakanText(array $rows): ?string
    {
        if (empty($rows)) return null;

        $parts = [];
        foreach ($rows as $row) {
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $name = null;

            if ($row['item_type'] === 'treatment') {
                $name = Treatment::query()->whereKey($row['treatment_id'])->value('nama');
            }

            if ($name) {
                $parts[] = $qty > 1 ? $name . " x{$qty}" : $name;
            }
        }

        return $parts ? implode(', ', $parts) : null;
    }

    private static function renderRegimenText(array $rows): ?string
    {
        if (empty($rows)) return null;

        $parts = [];
        foreach ($rows as $row) {
            $product = Product::find($row['product_id']);
            if (! $product) continue;
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $parts[] = $qty > 1 ? $product->nama . " x{$qty}" : $product->nama;
        }

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Replace the linked appointment's treatment items with the rows
     * submitted from the rekam medis edit modal. Consultation items are preserved.
     */
    private static function syncAppointmentTreatments(RekamMedis $record, array $rows): void
    {
        $appointment = $record->appointment;
        if (! $appointment) return;

        $appointment->items()->whereIn('item_type', ['treatment'])->delete();

        foreach ($rows as $row) {
            $type = $row['item_type'];
            $appointment->items()->create([
                'item_type'    => $type,
                'treatment_id' => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'qty'          => max(1, (int) ($row['qty'] ?? 1)),
            ]);
        }
    }
}
