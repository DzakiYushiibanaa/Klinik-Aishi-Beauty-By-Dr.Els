<?php

namespace App\Filament\Resources\RekamMedis\Tables;


use App\Models\Product;
use App\Models\RekamMedis;
use App\Models\Treatment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RekamMedisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('rekam_medis_id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                self::columnTanggalKunjungan(),
                self::columnPasien(),
                self::columnDokter(),
                self::columnTerapis(),
                self::columnJenisLayanan(),
                self::columnKeluhan(),
                self::columnDiagnosa(),
                self::columnTreatment(),
                self::columnRegimen(),
                self::columnLampiran(),
            ])
            ->filters(self::filters())
            ->recordActions([
                self::actionView(),
                self::actionDownload(),
                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->tooltip('Edit Rekam Medis')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading(fn (RekamMedis $record) => 'Edit Rekam Medis ' . ($record->no_kunjungan ?? '-'))
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batal')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->fillForm(fn (RekamMedis $record) => self::hydrateEditForm($record))
                    ->schema([
                        Section::make('Observasi Medis')
                            ->description('Identitas pasien, dokter, terapis, dan appointment tidak dapat diubah dari sini.')
                            ->columns(1)
                            ->schema([
                                Textarea::make('keluhan')
                                    ->label('Keluhan')
                                    ->rows(3),

                                Textarea::make('diagnosa')
                                    ->label('Diagnosa')
                                    ->rows(3),

                                Repeater::make('tindakan_items')
                                    ->label('Tindakan (Treatment)')
                                    ->addActionLabel('+ Tambah Tindakan')
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('item_type')
                                                ->label('Tipe')
                                                ->options([
                                                    'treatment' => 'Treatment',
                                                ])
                                                ->default('treatment')
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(fn ($state, $set) => [
                                                    $set('treatment_id', null),
                                                ]),

                                            Select::make('treatment_id')
                                                ->label('Treatment')
                                                ->options(fn () => Treatment::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('nama')
                                                    ->get()
                                                    ->mapWithKeys(fn ($t) => [
                                                        $t->treatment_id => $t->nama . ' — Rp ' . number_format((int) $t->harga, 0, ',', '.'),
                                                    ])
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                                                ->required(fn (Get $get) => $get('item_type') === 'treatment')
                                                ->reactive(),


                                        ]),

                                        TextInput::make('qty')
                                            ->label('Qty')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->required(),
                                    ]),
                            ]),

                        Section::make('Lampiran Foto')
                            ->columns(2)
                            ->schema([
                                FileUpload::make('foto_sebelum_path')
                                    ->label('Foto Sebelum')
                                    ->disk('public')
                                    ->directory('rekam-medis/sebelum')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(5120),

                                FileUpload::make('foto_sesudah_path')
                                    ->label('Foto Sesudah')
                                    ->disk('public')
                                    ->directory('rekam-medis/sesudah')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(5120),
                            ]),

                        Section::make('Catatan & Regimen')
                            ->columns(1)
                            ->schema([
                                Repeater::make('regimen_items')
                                    ->label('Regimen / Produk Rekomendasi')
                                    ->addActionLabel('+ Tambah Produk Regimen')
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('product_id')
                                                ->label('Produk')
                                                ->options(fn () => Product::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('nama')
                                                    ->get()
                                                    ->mapWithKeys(fn ($p) => [
                                                        $p->product_id => $p->nama . ' — Rp ' . number_format((int) $p->harga, 0, ',', '.'),
                                                    ])
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->reactive()
                                                ->columnSpan(2),

                                            TextInput::make('qty')
                                                ->label('Qty')
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1)
                                                ->required(),
                                        ]),
                                    ]),

                                Textarea::make('catatan_lanjutan')
                                    ->label('Catatan Lanjutan')
                                    ->rows(3)
                                    ->placeholder('Instruksi follow-up atau catatan khusus...'),
                            ]),
                    ])
                    ->mutateFormDataUsing(function (array $data, RekamMedis $record): array {
                        return self::prepareSaveData($data, $record);
                    })
                    ->after(function (RekamMedis $record, array $data) {
                        self::syncAppointmentTreatments($record, $data['_tindakan_items_cache'] ?? []);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ═══════════════════════════════════════════════════════
    // COLUMNS
    // ═══════════════════════════════════════════════════════

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

    private static function filters(): array
    {
        return [
            SelectFilter::make('jenis')
                ->label('Jenis Kunjungan')
                ->options([
                    'konsultasi' => 'Consultation',
                    'treatment'  => 'Treatment',
                    'follow_up'  => 'Follow-up',
                ])
                ->query(function (Builder $query, array $data) {
                    if (filled($data['value'] ?? null)) {
                        $query->whereHas('appointment', fn ($q) => $q->where('jenis', $data['value']));
                    }
                }),
        ];
    }

    // ═══════════════════════════════════════════════════════
    // ROW ACTIONS
    // ═══════════════════════════════════════════════════════

    private static function actionView(): Action
    {
        return Action::make('view_rekam_medis')
            ->label('')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->tooltip('Lihat Detail Rekam Medis')
            ->slideOver()
            ->stickyModalFooter()
            ->modalHeading(fn (RekamMedis $record) => 'Rekam Medis ' . ($record->no_kunjungan ?? '-'))
            ->modalWidth('2xl')
            ->modalCancelActionLabel('Tutup')
            ->modalSubmitAction(false)
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalContent(function (RekamMedis $record) {
                $rm = RekamMedis::with([
                    'appointment.items.treatment',

                    'appointment.items.consultation',
                    'appointment.items.handler',
                    'pasien.pasienProfile',
                    'dokter',
                    'terapis',
                ])->find($record->rekam_medis_id);

                return view('filament.rekam-medis.detail-slideover', ['rm' => $rm]);
            });
    }

    private static function actionDownload(): Action
    {
        return Action::make('download_attachments')
            ->label('')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->tooltip('Unduh Lampiran Foto')
            ->visible(fn (RekamMedis $record) => $record->foto_sebelum_path || $record->foto_sesudah_path)
            ->action(function (RekamMedis $record) {
                // If only one attachment exists, stream it directly.
                $paths = array_filter([$record->foto_sebelum_path, $record->foto_sesudah_path]);

                if (count($paths) === 1) {
                    $path = $paths[0];
                    if (Storage::disk('public')->exists($path)) {
                        return Storage::disk('public')->download($path);
                    }
                }

                // Multiple attachments → bundle into a zip.
                $zipName = ($record->no_kunjungan ?: 'rekam-medis-'.$record->rekam_medis_id).'.zip';
                $tmpPath = storage_path('app/tmp/'.$zipName);

                if (!is_dir(dirname($tmpPath))) {
                    @mkdir(dirname($tmpPath), 0775, true);
                }

                $zip = new \ZipArchive();
                if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($paths as $p) {
                        if (Storage::disk('public')->exists($p)) {
                            $zip->addFile(Storage::disk('public')->path($p), basename($p));
                        }
                    }
                    $zip->close();
                }

                return response()->download($tmpPath)->deleteFileAfterSend();
            });
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Resolve nama treatment / bundle dari rekam medis.
     *
     * Aturan:
     *   - Prioritas 1: field `tindakan` yang diisi manual oleh dokter
     *     (boleh berisi catatan / treatment custom).
     *   - Prioritas 2: appointment items dengan tipe `treatment`.
     *   - Item `consultation` SENGAJA DI-SKIP karena konsultasi bukan
     *     treatment medis. Untuk RM dari appointment konsultasi murni
     *     yang belum diisi tindakan, method ini mengembalikan null
     *     supaya UI menampilkan strip ("—").
     *
     * Format output saat ada lebih dari satu item: "Nama + N item".
     */
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
