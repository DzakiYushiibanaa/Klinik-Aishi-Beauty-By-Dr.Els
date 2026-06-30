<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Bundle;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\User;
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

/**
 * Form schema untuk modal "Mulai Layanan" pada tabel Appointments.
 *
 * Workflow yang ingin dicapai:
 *   - Dokter / terapis fokus pada pemeriksaan + diagnosa + rekomendasi.
 *   - Pembayaran TIDAK ditangani di sini — diteruskan ke admin/frontdesk
 *     melalui order yang otomatis dibuat oleh StartServiceFinalizer
 *     dengan default `status = menunggu_pembayaran`.
 *   - Untuk appointment konsultasi, dokter bisa menambahkan treatment
 *     lanjutan di hari yang sama via section "Treatment Lanjutan Hari Ini"
 *     (toggle-gated) tanpa perlu membuat appointment baru.
 *
 * Output ke StartServiceFinalizer::complete():
 *   consultation_id (jika konsultasi), treatment_items[] (dengan handler
 *   per item), keluhan, diagnosa, foto_sebelum_path, foto_sesudah_path,
 *   produk_rekomendasi_items[], catatan_lanjutan.
 */
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
                        $nama = e($appointment->pasien?->name ?? '-');
                        $rm   = e($appointment->pasien?->pasienProfile?->no_rekam_medis ?? '');

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
                            'diajukan'    => ['Diajukan',     '#FEF3C7', '#92400E'],
                            'akan_datang' => ['Disetujui',    '#DCFCE7', '#166534'],
                            'disetujui'   => ['Disetujui',    '#DCFCE7', '#166534'],
                            'diproses'    => ['Disetujui',    '#DCFCE7', '#166534'],
                            'selesai'     => ['Selesai',      '#D1FAE5', '#065F46'],
                            'batal'       => ['Dibatalkan',   '#FEE2E2', '#991B1B'],
                        ];
                        [$statusLabel, $statusBg, $statusColor] = $statusMap[strtolower((string) $appointment->status)]
                            ?? ['Diproses', '#DBEAFE', '#1E40AF'];

                        return new HtmlString('
                            <div style="padding:18px 20px; background:linear-gradient(135deg,#FAF8F5 0%,#FFFFFF 100%); border:1px solid #EDE8E3; border-radius:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                                    <div style="min-width:0;">
                                        <div style="font-size:1.15rem; font-weight:700; color:#1A1A1A; line-height:1.3;">'.$nama.'</div>
                                        '.($rm ? '<div style="font-size:0.85rem; color:#8A8078; margin-top:4px; font-variant-numeric:tabular-nums;">'.$rm.'</div>' : '').'
                                        <div style="display:flex; gap:14px; align-items:center; margin-top:10px; flex-wrap:wrap;">
                                            <span style="display:inline-flex; align-items:center; gap:6px; font-size:0.875rem; color:#1A1A1A; font-weight:500;">
                                                <svg width="16" height="16" fill="none" stroke="#B8956F" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                '.e($jenisLabel).'
                                            </span>
                                            <span style="font-size:0.875rem; color:#8A8078;">·</span>
                                            <span style="display:inline-flex; align-items:center; gap:6px; font-size:0.875rem; color:#1A1A1A;">
                                                <svg width="16" height="16" fill="none" stroke="#B8956F" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                '.e($tanggal).' • '.e($jam).' WIB
                                            </span>
                                        </div>
                                    </div>
                                    <span style="display:inline-block; padding:5px 12px; background:'.$statusBg.'; color:'.$statusColor.'; font-size:0.75rem; font-weight:600; border-radius:9999px; flex-shrink:0;">
                                        '.e($statusLabel).'
                                    </span>
                                </div>
                            </div>
                        ');
                    }),
            ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SECTION 2: DETAIL LAYANAN
    //
    //  Konsultasi → hanya Select konsultasi.
    //  Treatment  → Repeater treatment_items (treatment utama).
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
                Placeholder::make('keluhan_readonly')
                    ->label('Keluhan Pasien')
                    ->visible(fn () => self::resolveKeluhanFromContext($appointment) !== null)
                    ->content(function () use ($appointment): HtmlString {
                        $text = self::resolveKeluhanFromContext($appointment);
                        return new HtmlString(
                            '<div style="padding:12px 14px; background:#FAF8F5; border-left:3px solid #B8956F; border-radius:8px; font-size:0.95rem; line-height:1.6; color:#1A1A1A; white-space:pre-wrap;">'
                            .nl2br(e($text ?? '-'))
                            .'</div>'
                        );
                    }),

                Textarea::make('keluhan')
                    ->label('Keluhan Pasien')
                    ->rows(3)
                    ->autosize()
                    ->placeholder('Catat keluhan utama pasien...')
                    ->visible(fn () => self::resolveKeluhanFromContext($appointment) === null),

                Textarea::make('diagnosa')
                    ->label('Diagnosa / Hasil Pemeriksaan')
                    ->rows(4)
                    ->autosize()
                    ->required()
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
    //
    //  Hanya untuk appointment konsultasi. Toggle ON membuka repeater
    //  treatment + estimasi subtotal real-time. Treatment yang ditambah
    //  ikut tersimpan ke appointment_items dan order saat finalisasi
    //  via StartServiceFinalizer (tanpa perlu membuat appointment baru).
    // ═══════════════════════════════════════════════════════════
    private static function sectionTreatmentLanjutan(Appointment $appointment): Section
    {
        $hasExistingItems = $appointment->items()
            ->whereIn('item_type', ['treatment', 'bundle'])
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
                        $total = self::calculateTreatmentSubtotal($items);
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
                [$defaultType, $defaultId] = self::resolveDefaultHandler($appointment);

                return $appointment->items()
                    ->whereIn('item_type', ['treatment', 'bundle'])
                    ->get()
                    ->map(fn ($i) => [
                        'item_type'        => $i->item_type,
                        'treatment_id'     => $i->treatment_id,
                        'bundle_id'        => $i->bundle_id,
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
                    Select::make('item_type')
                        ->label('Tipe')
                        ->options([
                            'treatment' => 'Treatment',
                            'bundle'    => 'Bundle',
                        ])
                        ->default('treatment')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, $set) => [
                            $set('treatment_id', null),
                            $set('bundle_id', null),
                            $set('handled_by_type', null),
                            $set('handled_by_id', null),
                        ]),

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
                        ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                        ->required(fn (Get $get) => $get('item_type') === 'treatment')
                        ->reactive()
                        ->columnSpan(['md' => 2])
                        ->afterStateUpdated(function ($state, Set $set, Get $get) use ($appointment) {
                            $allowedTypes = self::resolveAllowedHandlerTypes((int) $state);
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

                            $defaultId = self::resolveDefaultHandlerIdForTreatment((int) $state, $appointment);
                            $set('handled_by_id', $defaultId);
                            $set('handled_by_type', $defaultId ? self::resolveSelectedHandlerType($defaultId) : null);
                        }),

                    Select::make('bundle_id')
                        ->label('Bundle')
                        ->options(fn () => Bundle::query()
                            ->where('is_active', true)
                            ->orderBy('nama')
                            ->get()
                            ->mapWithKeys(function ($b) {
                                $price = (int) ($b->harga_promo ?: $b->harga_normal);
                                return [$b->bundle_id => $b->nama . ' — Rp ' . number_format($price, 0, ',', '.')];
                            })
                        )
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('item_type') === 'bundle')
                        ->required(fn (Get $get) => $get('item_type') === 'bundle')
                        ->reactive()
                        ->columnSpan(['md' => 2]),
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
                        ->placeholder(fn (Get $get): string => self::isDoctorOnlyTreatment((int) $get('treatment_id'))
                            ? 'Pilih dokter'
                            : 'Pilih petugas')
                        ->options(fn (Get $get): array => self::handlerOptionsForTreatment((int) $get('treatment_id')))
                        ->searchable()
                        ->native(false)
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    if ($get('item_type') !== 'treatment' || filled($value)) {
                                        return;
                                    }

                                    $fail(self::isDoctorOnlyTreatment((int) $get('treatment_id'))
                                        ? 'Pilih dokter yang menangani treatment ini.'
                                        : 'Pilih dokter/terapis yang menangani treatment ini.');
                                };
                            },
                        ])
                        ->dehydrated()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Set $set) => $set('handled_by_type', $state ? self::resolveSelectedHandlerType((int) $state) : null))
                        ->helperText(fn (Get $get): ?string => self::handlerHelperText((int) $get('treatment_id')))
                        ->columnSpan(['md' => 2]),
                ]),
            ]);
    }

    /**
     * Hitung total estimasi tagihan dari array treatment_items
     * yang masih dalam state form (belum tersimpan ke DB).
     *
     * Treatment → harga_treatment × qty.
     * Bundle    → (harga_promo ?: harga_normal) × qty.
     */
    private static function calculateTreatmentSubtotal(array $items): int
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

        $bundleIds = collect($items)
            ->pluck('bundle_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $treatmentPrices = $treatmentIds
            ? Treatment::whereIn('treatment_id', $treatmentIds)->pluck('harga', 'treatment_id')->all()
            : [];

        $bundles = $bundleIds
            ? Bundle::whereIn('bundle_id', $bundleIds)
                ->get(['bundle_id', 'harga_promo', 'harga_normal'])
                ->keyBy('bundle_id')
            : collect();

        $total = 0;
        foreach ($items as $row) {
            $type = $row['item_type'] ?? 'treatment';
            $qty  = max(1, (int) ($row['qty'] ?? 1));

            if ($type === 'treatment' && ! empty($row['treatment_id'])) {
                $price  = (int) ($treatmentPrices[$row['treatment_id']] ?? 0);
                $total += $price * $qty;
            } elseif ($type === 'bundle' && ! empty($row['bundle_id'])) {
                $b = $bundles->get($row['bundle_id']);
                if ($b) {
                    $price  = (int) ($b->harga_promo ?: $b->harga_normal);
                    $total += $price * $qty;
                }
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
    private static function resolveAllowedHandlerTypes(?int $treatmentId): array
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
    private static function activeStaffOptions(string $type): array
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

    private static function handlerOptionsForTreatment(?int $treatmentId): array
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

    private static function resolveDefaultHandlerIdForTreatment(?int $treatmentId, Appointment $appointment): ?int
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

    private static function resolveSelectedHandlerType(?int $userId): ?string
    {
        if (! $userId) return null;

        $user = User::find($userId);
        if (! $user) return null;

        if ($user->hasRole('dokter')) return 'dokter';
        if ($user->hasRole('terapis')) return 'terapis';

        return null;
    }

    private static function isDoctorOnlyTreatment(?int $treatmentId): bool
    {
        return self::resolveAllowedHandlerTypes($treatmentId) === ['dokter'];
    }

    private static function handlerHelperText(?int $treatmentId): ?string
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
    private static function resolveDefaultHandler(Appointment $appointment): array
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

    /**
     * Ambil keluhan pasien dari konteks appointment / pra-konsultasi.
     *
     * Sumber prioritas:
     *   1. patient_form (pra-konsul) yang ter-link → keluhan_utama
     *   2. catatan_pasien di appointment
     *
     * Return null kalau tidak ada → form akan render textarea editable.
     */
    private static function resolveKeluhanFromContext(Appointment $appointment): ?string
    {
        $appointment->loadMissing('patientForm');
        if ($keluhan = $appointment->patientForm?->keluhan_utama) {
            $clean = trim((string) $keluhan);
            if ($clean !== '') return $clean;
        }
        $catatan = trim((string) ($appointment->catatan_pasien ?? ''));
        return $catatan !== '' ? $catatan : null;
    }

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
        if ($type === 'bundle' && ! empty($state['bundle_id'])) {
            $b = Bundle::find($state['bundle_id']);
            if ($b) return 'Bundle: '.$b->nama;
        }
        return null;
    }
}
