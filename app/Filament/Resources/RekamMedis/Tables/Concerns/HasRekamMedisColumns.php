<?php

namespace App\Filament\Resources\RekamMedis\Tables\Concerns;

use App\Models\RekamMedis;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait HasRekamMedisColumns
{
    private static function columnTanggalKunjungan(): TextColumn
    {
        return TextColumn::make('tanggal_kunjungan')
            ->label('Tanggal Kunjungan')
            ->html()
            ->state(function (RekamMedis $record) {
                $dt = $record->appointment?->tanggal_jam
                    ? Carbon::parse($record->appointment->tanggal_jam)
                    : Carbon::parse($record->created_at);
                $tanggal = $dt ? $dt->translatedFormat('d M Y') : '-';
                $kode = e($record->no_kunjungan ?? '-');

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.e($tanggal).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px; font-variant-numeric:tabular-nums;">'.$kode.'</span>
                    </div>
                ';
            })
            ->searchable(query: fn (Builder $q, string $s) => $q->where('no_kunjungan', 'like', "%{$s}%"))
            ->sortable(query: function (Builder $q, string $d) {
                return $q->leftJoin('appointments', 'rekam_medis.appointment_id', '=', 'appointments.appointment_id')
                    ->orderBy('appointments.tanggal_jam', $d)
                    ->select('rekam_medis.*');
            });
    }

    private static function columnPasien(): TextColumn
    {
        return TextColumn::make('pasien.name')
            ->label('Pasien')
            ->html()
            ->state(function (RekamMedis $record) {
                $nama = e($record->pasien?->name ?? '-');
                $rm = e($record->pasien?->pasienProfile?->no_rekam_medis ?? '-');
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.$nama.'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px; font-variant-numeric:tabular-nums;">'.$rm.'</span>
                    </div>
                ';
            })
            ->searchable(query: function (Builder $query, string $search) {
                return $query->whereHas('pasien', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pasien.pasienProfile', fn ($q) => $q->where('no_rekam_medis', 'like', "%{$search}%"));
            });
    }

    private static function columnDokter(): TextColumn
    {
        return TextColumn::make('dokter.name')
            ->label('Dokter')
            ->html()
            ->state(function (RekamMedis $record) {
                $nama = e($record->dokter?->name ?? '—');
                $sub  = self::resolveLayananDetail($record);

                $subHtml = $sub
                    ? '<span style="font-size:0.8125rem; color:#B8956F; font-weight:500; margin-top:2px;">'.e($sub).'</span>'
                    : '';

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:600; color:#111827;">'.$nama.'</span>
                        '.$subHtml.'
                    </div>
                ';
            })
            ->searchable(query: fn (Builder $q, string $s) => $q->whereHas('dokter', fn ($qq) => $qq->where('name', 'like', "%{$s}%")));
    }

    private static function columnTerapis(): TextColumn
    {
        return TextColumn::make('terapis_handler')
            ->label('Terapis')
            ->html()
            ->state(function (RekamMedis $record) {
                $names = self::resolveTerapisHandlers($record);

                if ($names->isEmpty()) {
                    return '<span style="color:#8A8078; font-size:0.8125rem;">—</span>';
                }

                $first = e($names->first());
                $rest = $names->count() - 1;
                $sub = $rest > 0
                    ? '<span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">+'.(int) $rest.' terapis lain</span>'
                    : '<span style="font-size:0.8125rem; color:#B8956F; font-weight:500; margin-top:2px;">Menangani treatment</span>';

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:600; color:#111827;">'.$first.'</span>
                        '.$sub.'
                    </div>
                ';
            })
            ->tooltip(fn (RekamMedis $record) => self::resolveTerapisHandlers($record)->implode(', '))
            ->searchable(query: function (Builder $query, string $search) {
                return $query
                    ->whereHas('terapis', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('appointment.items', function ($q) use ($search) {
                        $q->where('handled_by_type', 'terapis')
                            ->whereHas('handler', fn ($handler) => $handler->where('name', 'like', "%{$search}%"));
                    });
            });
    }

    private static function resolveTerapisHandlers(RekamMedis $record): \Illuminate\Support\Collection
    {
        $itemHandlers = $record->appointment?->items
            ?->filter(fn ($item) => strtolower((string) $item->handled_by_type) === 'terapis' && $item->handler)
            ->map(fn ($item) => $item->handler->name)
            ->filter()
            ->unique()
            ->values()
            ?? collect();

        if ($itemHandlers->isNotEmpty()) {
            return $itemHandlers;
        }

        return collect([$record->terapis?->name])->filter()->values();
    }

    /**
     * Detail layanan untuk sub-text kartu Dokter di tabel & slideover.
     *
     * Aturan:
     *   - Konsultasi: kembalikan nama konsultasi spesifik (contoh:
     *     "Konsultasi Awal", "Konsultasi Lanjutan") dari relasi
     *     AppointmentItem -> Consultation.
     *     Fallback ke "Konsultasi" generic kalau tidak terhubung ke
     *     consultation_id yang valid.
     *   - Treatment: kembalikan "Treatment" (nama treatment-nya sudah
     *     muncul di kolom Treatment terpisah).
     *   - Follow-up / Injection / Procedure: kembalikan label-nya.
     *   - Tidak ada appointment: null.
     */
    private static function resolveLayananDetail(RekamMedis $record): ?string
    {
        $jenis = strtolower((string) ($record->appointment?->jenis ?? ''));
        if ($jenis === '') {
            return null;
        }

        if ($jenis === 'konsultasi') {
            $item = $record->appointment?->items
                ?->first(fn ($i) => strtolower((string) $i->item_type) === 'consultation');

            $namaKonsul = $item?->consultation?->nama;
            return $namaKonsul ?: 'Konsultasi';
        }

        return match ($jenis) {
            'treatment'           => 'Treatment',
            'follow_up', 'follow-up' => 'Follow-up',
            'injection'           => 'Injection',
            'procedure'           => 'Procedure',
            default               => ucfirst($jenis),
        };
    }

    /**
     * Jenis layanan — diambil dari appointment->jenis (sumber kebenaran)
     * dengan fallback ke item pertama appointment kalau jenis NULL.
     *
     * Output:
     *   - "Konsultasi" untuk jenis = konsultasi
     *   - "Treatment" untuk jenis = treatment (dengan nama treatment di sub-text)
     *   - "Follow-up" / "Injection" / "Procedure" untuk jenis lain
     *   - "—" untuk RM yang tidak punya appointment
     */
    private static function columnJenisLayanan(): TextColumn
    {
        return TextColumn::make('appointment.jenis')
            ->label('Jenis Layanan')
            ->html()
            ->state(function (RekamMedis $record) {
                $jenis = strtolower((string) ($record->appointment?->jenis ?? ''));

                if ($jenis === '' && $record->appointment?->items?->isNotEmpty()) {
                    $firstType = strtolower((string) $record->appointment->items->first()->item_type);
                    $jenis = match ($firstType) {
                        'consultation' => 'konsultasi',
                        'treatment' => 'treatment',
                        default => '',
                    };
                }

                if ($jenis === '') {
                    return '<span style="color:#8A8078; font-size:0.8125rem;">—</span>';
                }

                $jenisMap = [
                    'konsultasi' => ['Konsultasi', '#DBEAFE', '#1E40AF'],
                    'treatment'  => ['Treatment',  '#FCE7E9', '#9F1239'],
                    'follow_up'  => ['Follow-up',  '#E0E7FF', '#3730A3'],
                    'follow-up'  => ['Follow-up',  '#E0E7FF', '#3730A3'],
                    'injection'  => ['Injection',  '#FEF3C7', '#92400E'],
                    'procedure'  => ['Procedure',  '#D1FAE5', '#065F46'],
                ];
                [$label, $bg, $color] = $jenisMap[$jenis] ?? [ucfirst($jenis), '#E5E7EB', '#4B5563'];

                return '
                    <span style="display:inline-block; padding:3px 10px; border-radius:9999px; background:'.$bg.'; color:'.$color.'; font-size:0.75rem; font-weight:600;">
                        '.e($label).'
                    </span>
                ';
            })
            ->sortable(query: function (Builder $q, string $d) {
                return $q->leftJoin('appointments as ap_jenis', 'rekam_medis.appointment_id', '=', 'ap_jenis.appointment_id')
                    ->orderBy('ap_jenis.jenis', $d)
                    ->select('rekam_medis.*');
            });
    }

    private static function columnKeluhan(): TextColumn
    {
        return TextColumn::make('keluhan')
            ->label('Keluhan')
            ->html()
            ->state(function (RekamMedis $record) {
                $text = $record->keluhan ? Str::limit(strip_tags($record->keluhan), 22) : '—';
                $color = $record->keluhan ? '#111827' : '#8A8078';
                return '<span style="color:'.$color.'; font-size:0.875rem;">'.e($text).'</span>';
            })
            ->tooltip(fn (RekamMedis $record) => $record->keluhan ? strip_tags($record->keluhan) : null)
            ->searchable();
    }

    private static function columnDiagnosa(): TextColumn
    {
        return TextColumn::make('diagnosa')
            ->label('Diagnosa')
            ->html()
            ->state(function (RekamMedis $record) {
                $text = $record->diagnosa ? Str::limit(strip_tags($record->diagnosa), 22) : '—';
                $color = $record->diagnosa ? '#111827' : '#8A8078';
                return '<span style="color:'.$color.'; font-size:0.875rem;">'.e($text).'</span>';
            })
            ->tooltip(fn (RekamMedis $record) => $record->diagnosa ? strip_tags($record->diagnosa) : null)
            ->searchable();
    }

    private static function columnTreatment(): TextColumn
    {
        return TextColumn::make('tindakan')
            ->label('Treatment')
            ->html()
            ->state(function (RekamMedis $record) {
                $treatment = self::resolveTreatmentName($record);
                if (!$treatment) {
                    return '<span style="color:#8A8078; font-size:0.8125rem;">—</span>';
                }
                return '<span style="font-weight:600; color:#111827; font-size:0.875rem; line-height:1.35; display:inline-block;">'.e(Str::limit($treatment, 28)).'</span>';
            })
            ->tooltip(fn (RekamMedis $record) => self::resolveTreatmentName($record))
            ->searchable();
    }

    private static function columnRegimen(): TextColumn
    {
        return TextColumn::make('produk_rekomendasi')
            ->label('Regimen')
            ->html()
            ->state(function (RekamMedis $record) {
                if (!$record->produk_rekomendasi) {
                    return '<span style="color:#8A8078; font-size:0.8125rem;">—</span>';
                }
                $clean = strip_tags($record->produk_rekomendasi);
                return '<span style="color:#111827; font-size:0.875rem; line-height:1.35; display:inline-block;">'.e(Str::limit($clean, 26)).'</span>';
            })
            ->tooltip(fn (RekamMedis $record) => $record->produk_rekomendasi ? strip_tags($record->produk_rekomendasi) : null);
    }

    private static function columnLampiran(): TextColumn
    {
        return TextColumn::make('lampiran')
            ->label('Lampiran')
            ->html()
            ->alignCenter()
            ->state(function (RekamMedis $record) {
                $hasAttachments = $record->foto_sebelum_path || $record->foto_sesudah_path;

                if (!$hasAttachments) {
                    return '<span style="color:#8A8078; font-size:0.8125rem;">—</span>';
                }

                $count = (int) (bool) $record->foto_sebelum_path + (int) (bool) $record->foto_sesudah_path;

                return '
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:10px; background:#FEF7E6; border:1px solid #F3E4C4; position:relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#B8956F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                            <circle cx="12" cy="13" r="3"/>
                        </svg>
                        <span style="position:absolute; top:-4px; right:-4px; min-width:14px; height:14px; padding:0 4px; background:#EF4444; color:#fff; border-radius:9999px; font-size:0.625rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; border:2px solid #fff;">'.$count.'</span>
                    </div>
                ';
            });
    }

    // ═══════════════════════════════════════════════════════
    // FILTERS
    // ═══════════════════════════════════════════════════════

    private static function resolveTreatmentName(RekamMedis $record): ?string
    {
        // 1) Prefer explicit "tindakan" field if filled.
        if (!empty($record->tindakan)) {
            return strip_tags($record->tindakan);
        }

        // 2) Fallback ke appointment items, hanya tipe treatment.
        $items = $record->appointment?->items;
        if (!$items || $items->isEmpty()) {
            return null;
        }

        $treatmentItems = $items->filter(
            fn ($i) => in_array(strtolower((string) $i->item_type), ['treatment'], true)
        )->values();

        if ($treatmentItems->isEmpty()) {
            return null;
        }

        $first = $treatmentItems->first();
        $name = match (true) {
            strtolower((string) $first->item_type) === 'treatment' && $first->treatment => $first->treatment->nama,
            default => null,
        };

        if (!$name) {
            return null;
        }

        $rest = $treatmentItems->count() - 1;
        return $rest > 0 ? $name.' + '.$rest.' item' : $name;
    }

    // ═══════════════════════════════════════════════════════
    // EDIT HELPERS (form hydrate + save)
    // ═══════════════════════════════════════════════════════

    /**
     * Hydrate the edit form:
     * - tindakan_items: read from appointment_items (treatment only)
     * - regimen_items:  parse from produk_rekomendasi text if stored as "Nama x2, Nama2"
     *   (best-effort — matching against existing active products)
     */
}
