<?php

namespace App\Filament\Resources\RekamMedis\Tables\Concerns;

use App\Models\RekamMedis;
use Filament\Actions\Action;
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
use Illuminate\Support\Facades\Storage;

trait HasRekamMedisActions
{
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
    private static function actionEditRekamMedis(): \Filament\Actions\EditAction
    {
        return EditAction::make()
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
                    });
    }
}
