<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;

use App\Models\Consultation;
use App\Models\Product;
use App\Models\Treatment;

use App\Services\Appointments\TreatmentHandlerResolver;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
class MulaiLayananForm
{
    /**
     * @return array<int,\Filament\Schemas\Components\Component>
     */
    public static function schema(Appointment $appointment): array
    {
        $schema = [
            self::sectionRingkasanPasien($appointment),
            self::sectionDetailLayanan($appointment),
            self::sectionHasilPemeriksaan($appointment),
            self::sectionDokumentasiFoto(),
            self::sectionRekomendasiCatatan(),
        ];

        if ($appointment->jenis === 'konsultasi') {
            array_splice($schema, 4, 0, [self::sectionTreatmentLanjutan($appointment)]);
        }

        return $schema;
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 1: RINGKASAN PASIEN
    // ═══════════════════════════════════════════════════════════
    private static function sectionRingkasanPasien(Appointment $appointment): Section
    {
        return Section::make('Ringkasan Pasien')
            ->compact()
            ->schema([
                Placeholder::make('pasien_card')
                    ->hiddenLabel()
                    ->content(function () use ($appointment): HtmlString {
                        $nama = $appointment->pasien?->name ?? '-';
                        $rm   = $appointment->pasien?->pasienProfile?->no_rekam_medis ?? '';

                        $jenis = strtolower((string) $appointment->jenis);
                        $jenisLabel = match ($jenis) {
                            'konsultasi' => 'Konsultasi',
                            'treatment'  => 'Treatment',
                            default      => ucfirst($jenis ?: 'Layanan'),
                        };

                        $tanggal = $appointment->tanggal_jam
                            ? \Illuminate\Support\Carbon::parse($appointment->tanggal_jam)
                                ->locale('id')
                                ->translatedFormat('d M Y')
                            : '-';
                        $jam = $appointment->tanggal_jam
                            ? \Illuminate\Support\Carbon::parse($appointment->tanggal_jam)->format('H:i')
                            : '-';

                        $statusMap = [
                            'akan_datang' => ['Disetujui',    '#DCFCE7', '#166534'],
                            'disetujui'   => ['Disetujui',    '#DCFCE7', '#166534'],
                            'diproses'    => ['Disetujui',    '#DCFCE7', '#166534'],
                            'selesai'     => ['Selesai',      '#D1FAE5', '#065F46'],
                            'batal'       => ['Dibatalkan',   '#FEE2E2', '#991B1B'],
                        ];
                        [$statusLabel, $statusBg, $statusColor] = $statusMap[strtolower((string) $appointment->status)]
                            ?? ['Diproses', '#DBEAFE', '#1E40AF'];

                        return new HtmlString(
                            view('filament.appointments.ringkasan-pasien-card', compact(
                                'nama', 'rm', 'jenisLabel', 'tanggal', 'jam',
                                'statusLabel', 'statusBg', 'statusColor'
                            ))->render()
                        );
                    }),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 2: DETAIL LAYANAN
    // ═══════════════════════════════════════════════════════════
    private static function sectionDetailLayanan(Appointment $appointment): Section
    {
        $components = [];

        if ($appointment->jenis === 'konsultasi') {
            $components[] = Select::make('consultation_id')
                ->label('Pilih Konsultasi')
                ->options(fn () => Consultation::query()
                    ->where('is_active', true)
                    ->get()
                    ->mapWithKeys(fn ($c) => [
                        $c->consultation_id => $c->nama . ' — Rp ' . number_format((int) $c->harga, 0, ',', '.'),
                    ])
                )
                ->searchable()
                ->preload()
                ->required()
                ->default(function () use ($appointment) {
                    return $appointment->items()
                        ->where('item_type', 'consultation')
                        ->value('consultation_id');
                });
        } else {
            // Appointment jenis treatment: treatment_items adalah konten utama.
            $components[] = self::treatmentItemsRepeater($appointment);
        }

        return Section::make('Detail Layanan')
            ->compact()
            ->schema($components);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 3: HASIL PEMERIKSAAN
    // ═══════════════════════════════════════════════════════════
    private static function sectionHasilPemeriksaan(Appointment $appointment): Section
    {
        return Section::make('Hasil Pemeriksaan')
            ->compact()
            ->schema([
                Textarea::make('diagnosa')
                    ->label('Diagnosa / Hasil Pemeriksaan')
                    ->rows(4)
                    ->autosize()
                    ->maxLength(2000)
                    ->placeholder('Tuliskan hasil pemeriksaan atau diagnosa pasien...'),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 4: DOKUMENTASI FOTO
    // ═══════════════════════════════════════════════════════════
    private static function sectionDokumentasiFoto(): Section
    {
        return Section::make('Dokumentasi Foto')
            ->compact()
            ->columns(2)
            ->schema([
                FileUpload::make('foto_sebelum_path')
                    ->label('Foto Sebelum')
                    ->helperText('Format JPG/PNG maksimal 5MB.')
                    ->disk('public')
                    ->directory('rekam-medis/sebelum')
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120),

                FileUpload::make('foto_sesudah_path')
                    ->label('Foto Sesudah')
                    ->helperText('Format JPG/PNG maksimal 5MB.')
                    ->disk('public')
                    ->directory('rekam-medis/sesudah')
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 5: TREATMENT LANJUTAN HARI INI
    // ═══════════════════════════════════════════════════════════
    private static function sectionTreatmentLanjutan(Appointment $appointment): Section
    {
        $hasExistingItems = $appointment->items()
            ->whereIn('item_type', ['treatment'])
            ->exists();

        return Section::make('Treatment Lanjutan Hari Ini')
            ->description('Pasien melanjutkan treatment setelah konsultasi.')
            ->compact()
            ->schema([
                Toggle::make('add_followup_treatments')
                    ->label('Aktifkan treatment lanjutan')
                    ->helperText('Aktifkan jika pasien langsung melanjutkan treatment di hari yang sama.')
                    ->default($hasExistingItems)
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, Set $set) {
                        // Bersihkan daftar treatment kalau toggle dimatikan
                        // supaya tidak ada baris tertinggal yang ikut tersubmit.
                        if (! $state) {
                            $set('treatment_items', []);
                        }
                    }),

                self::treatmentItemsRepeater($appointment)
                    ->visible(fn (Get $get): bool => (bool) $get('add_followup_treatments')),

                Placeholder::make('subtotal_estimasi')
                    ->hiddenLabel()
                    ->visible(fn (Get $get): bool => (bool) $get('add_followup_treatments'))
                    ->content(function (Get $get): HtmlString {
                        $items = (array) ($get('treatment_items') ?? []);
                        $total = TreatmentHandlerResolver::calculateTreatmentSubtotal($items);
                        return new HtmlString('
                            <div style="display:flex; justify-content:flex-end; align-items:baseline; gap:10px; padding:12px 0 4px; border-top:1px solid #EDE8E3; margin-top:8px;">
                                <span style="font-size:0.875rem; color:#8A8078;">Estimasi Subtotal</span>
                                <span style="font-size:1.15rem; font-weight:700; color:#1A1A1A; font-variant-numeric:tabular-nums;">Rp '.number_format($total, 0, ',', '.').'</span>
                            </div>
                        ');
                    }),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 6: REKOMENDASI & CATATAN
    // ═══════════════════════════════════════════════════════════
    private static function sectionRekomendasiCatatan(): Section
    {
        return Section::make('Rekomendasi & Catatan')
            ->compact()
            ->schema([
                Repeater::make('produk_rekomendasi_items')
                    ->label('Rekomendasi Produk / Skincare')
                    ->addActionLabel('+ Tambah Produk')
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
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
                                ->columnSpan(['md' => 2]),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                        ]),
                    ]),

                Textarea::make('catatan_lanjutan')
                    ->label('Catatan Tambahan')
                    ->rows(3)
                    ->autosize()
                    ->placeholder('Contoh: Gunakan sunscreen setiap pagi dan hindari exfoliating selama beberapa hari.'),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * Repeater treatment_items yang dipakai di Detail Layanan
     * (untuk appointment treatment) maupun di Treatment Lanjutan
     * (untuk appointment konsultasi). Field name tetap satu →
     * tidak konflik karena Section yang tidak visible akan
     * di-skip oleh Filament.
     */
    private static function treatmentItemsRepeater(Appointment $appointment): Repeater
    {
        return Repeater::make('treatment_items')
            ->label('Daftar Treatment')
            ->columnSpanFull()
            ->addActionLabel('+ Tambah Treatment')
            ->defaultItems(0)
            ->default(function () use ($appointment) {
                [$defaultType, $defaultId] = TreatmentHandlerResolver::resolveDefaultHandler($appointment);

                return $appointment->items()
                    ->whereIn('item_type', ['treatment'])
                    ->get()
                    ->map(fn ($i) => [
                        'item_type'        => $i->item_type,
                        'treatment_id'     => $i->treatment_id,

                        'qty'              => (int) ($i->qty ?? 1),
                        'handled_by_type'  => $i->handled_by_type ?: $defaultType,
                        'handled_by_id'    => $i->handled_by_id   ?: $defaultId,
                    ])
                    ->all();
            })
            ->reorderable(false)
            ->reactive()
            ->itemLabel(fn (array $state): ?string => self::resolveRepeaterItemLabel($state))
            ->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    Hidden::make('item_type')
                        ->default('treatment'),

                    Select::make('treatment_id')
                        ->label('Nama Treatment')
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
                        ->required()
                        ->reactive()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) use ($appointment) {
                            $allowedTypes = TreatmentHandlerResolver::resolveAllowedHandlerTypes((int) $state);
                            $currentType  = $get('handled_by_type');

                            // Reset kalau tipe yang sudah dipilih tidak valid lagi.
                            if ($currentType && ! in_array($currentType, $allowedTypes, true)) {
                                $set('handled_by_type', null);
                                $set('handled_by_id', null);
                                $currentType = null;
                            }

                            if ($currentType && $get('handled_by_id')) {
                                return; // sudah ada handler yang valid.
                            }

                            $defaultId = TreatmentHandlerResolver::resolveDefaultHandlerIdForTreatment((int) $state, $appointment);
                            $set('handled_by_id', $defaultId);
                            $set('handled_by_type', $defaultId ? TreatmentHandlerResolver::resolveSelectedHandlerType($defaultId) : null);
                        }),


                ]),

                Grid::make(['default' => 1, 'md' => 3])->schema([
                    TextInput::make('qty')
                        ->label('Jumlah Sesi')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required()
                        ->reactive(),

                    Hidden::make('handled_by_type')
                        ->dehydrated(),

                    Select::make('handled_by_id')
                        ->label('Dokter / Terapis')
                        ->placeholder(fn (Get $get): string => TreatmentHandlerResolver::isDoctorOnlyTreatment((int) $get('treatment_id'))
                            ? 'Pilih dokter'
                            : 'Pilih petugas')
                        ->options(fn (Get $get): array => TreatmentHandlerResolver::handlerOptionsForTreatment((int) $get('treatment_id')))
                        ->searchable()
                        ->native(false)
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    if ($get('item_type') !== 'treatment' || filled($value)) {
                                        return;
                                    }

                                    $fail(TreatmentHandlerResolver::isDoctorOnlyTreatment((int) $get('treatment_id'))
                                        ? 'Pilih dokter yang menangani treatment ini.'
                                        : 'Pilih dokter/terapis yang menangani treatment ini.');
                                };
                            },
                        ])
                        ->dehydrated()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Set $set) => $set('handled_by_type', $state ? TreatmentHandlerResolver::resolveSelectedHandlerType((int) $state) : null))
                        ->helperText(fn (Get $get): ?string => TreatmentHandlerResolver::handlerHelperText((int) $get('treatment_id')))
                        ->columnSpan(['md' => 2]),
                ]),
            ]);
    }


    // Remove resolveKeluhanFromContext

    /**
     * Label compact untuk row repeater saat ditutup.
     */
    private static function resolveRepeaterItemLabel(array $state): ?string
    {
        $type = $state['item_type'] ?? null;
        if ($type === 'treatment' && ! empty($state['treatment_id'])) {
            $t = Treatment::find($state['treatment_id']);
            if ($t) return $t->nama;
        }

        return null;
    }
}