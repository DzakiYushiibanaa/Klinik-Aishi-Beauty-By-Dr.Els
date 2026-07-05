<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;

use App\Models\Consultation;
use App\Models\JadwalPraktik;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AppointmentForm
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

        if ($record->jenis !== 'konsultasi' || ! $consultationId) {
            return;
        }

        $record->items()->create([
            'item_type'       => 'consultation',
            'consultation_id' => (int) $consultationId,
            'qty'             => 1,
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
            'items'   => array_values(array_filter((array) ($data['lanjut_items'] ?? []), function ($row) {
                $type = $row['item_type'] ?? null;
                return ($type === 'treatment' && ! empty($row['treatment_id']));
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

        if (! $pending || ! $pending['enabled']) {
            return;
        }

        foreach ($pending['items'] as $row) {
            $type = $row['item_type'];
            $record->items()->create([
                'item_type'       => $type,
                'treatment_id'    => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'qty'             => max(1, (int) ($row['qty'] ?? 1)),
                'handled_by_type' => $type === 'treatment' ? ($row['handled_by_type'] ?? null) : null,
                'handled_by_id'   => $type === 'treatment' && ! empty($row['handled_by_id'])
                    ? (int) $row['handled_by_id']
                    : null,
            ]);
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ═══════════════════════════════════════════
                // Section 1: Identitas dan Jadwal
                // ═══════════════════════════════════════════
                // ═══════════════════════════════════════════
                // Section 1: Identitas Pasien
                // ═══════════════════════════════════════════
                Section::make('Identitas Pasien')
                    ->schema([
                        Select::make('user_id')
                            ->label('Pasien')
                            ->relationship(
                                name: 'pasien',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) =>
                                    $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'pasien'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->default(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false)
                                ? Filament::auth()->id()
                                : null)
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Nama Lengkap'),
                                TextInput::make('email')->email()->label('Email'),
                                TextInput::make('phone')->tel()->label('Nomor Telepon (Opsional)'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $user = \App\Models\User::create([
                                    'name' => $data['name'],
                                    'email' => $data['email'] ?? null,
                                    'phone' => $data['phone'] ?? null,
                                    'password' => bcrypt('password123'),
                                ]);
                                $user->assignRole('pasien');
                                return $user->id;
                            })
                            ->columnSpanFull(),
                    ]),

                // ═══════════════════════════════════════════
                // Section 2: Layanan & Petugas Utama
                // ═══════════════════════════════════════════
                Section::make('Layanan & Petugas Utama')
                    ->schema([
                        ToggleButtons::make('jenis')
                            ->label('Jenis Layanan')
                            ->options([
                                'konsultasi' => 'Konsultasi',
                                'treatment' => 'Treatment',
                            ])
                            ->inline()
                            ->grouped()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('dokter_id', null);
                                $set('terapis_id', null);
                                $set('tanggal_jam', null);
                                $set('new_tanggal', null);
                                $set('new_jam', null);
                                $set('items', []);
                            }),

                        Select::make('dokter_id')
                            ->label('Pilih Dokter')
                            ->relationship(
                                name: 'dokter',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) =>
                                    $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'dokter'))
                                          ->whereHas('dokterProfile', fn($q) => $q->whereIn('status_praktik', ['aktif', 'Aktif']))
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('jenis') === 'konsultasi')
                            ->visible(fn (Get $get) => $get('jenis') === 'konsultasi')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => [
                                $set('tanggal_jam', null),
                                $set('new_tanggal', null),
                                $set('new_jam', null)
                            ]),
                    ])->columns(2),

                // ═══════════════════════════════════════════
                // Section 3: Rincian Layanan
                // ═══════════════════════════════════════════
                Section::make('Rincian Layanan')
                    ->schema([
                        // ── Konsultasi ──
                        Select::make('consultation_id')
                            ->label('Pilih Konsultasi')
                            ->options(fn () => Consultation::query()
                                ->where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($c) => [
                                    $c->consultation_id => $c->nama . ' — Rp ' . number_format($c->harga, 0, ',', '.') . ' (' . $c->durasi_menit . ' menit)'
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('jenis') === 'konsultasi')
                            ->visible(fn (Get $get) => $get('jenis') === 'konsultasi')
                            ->reactive()
                            ->afterStateHydrated(function (Select $component, ?Appointment $record) {
                                if (! $record) return;
                                $item = $record->items()
                                    ->where('item_type', 'consultation')
                                    ->whereNotNull('consultation_id')
                                    ->first();
                                if ($item) {
                                    $component->state($item->consultation_id);
                                }
                            }),

                        // ── Treatment Repeater ──
                        Repeater::make('items')
                            ->label('Daftar Treatment')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    Hidden::make('item_type')
                                        ->default('treatment'),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->relationship('treatment', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $set, Get $get) {
                                            $handlerId = self::defaultHandlerIdForTreatment((int) $state);
                                            $set('handled_by_id', $handlerId);
                                            $set('handled_by_type', $handlerId ? self::resolveSelectedHandlerType($handlerId) : null);
                                        }),
                                ]),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),

                                Hidden::make('handled_by_type')
                                    ->dehydrated(),

                                Select::make('handled_by_id')
                                    ->label('Ditangani Oleh (Dokter / Terapis)')
                                    ->placeholder('Pilih petugas')
                                    ->options(fn (Get $get): array => self::handlerOptionsForTreatment((int) $get('treatment_id')))
                                    ->searchable()
                                    ->native(false)
                                    ->visible(fn (Get $get) => ! (Filament::auth()->user()?->hasRole('pasien') ?? false))
                                    ->required(fn (Get $get) => ! (Filament::auth()->user()?->hasRole('pasien') ?? false))
                                    ->validationMessages([
                                        'required' => 'Pilih dokter/terapis yang menangani treatment ini.',
                                    ])
                                    ->disabled(fn (Get $get): bool => self::isDoctorOnlyTreatment((int) $get('treatment_id')))
                                    ->dehydrated()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set) => $set('handled_by_type', $state ? self::resolveSelectedHandlerType((int) $state) : null))
                                    ->helperText(fn (Get $get): ?string => self::handlerHelperText((int) $get('treatment_id'))),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->reorderable(false)
                            ->visible(fn (Get $get) => $get('jenis') === 'treatment')
                            ->required(fn (Get $get) => $get('jenis') === 'treatment')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, $set) {
                                self::ensureSelectedTerapisEligible($get, $set);
                                $set('tanggal_jam', null);
                            }),
                    ]),

                // ═══════════════════════════════════════════
                // Section 4: Pilih Jadwal
                // ═══════════════════════════════════════════
                Section::make('Pilih Jadwal')
                    ->schema([
                        DateTimePicker::make('tanggal_jam')
                            ->label('Tanggal & Jam')
                            ->required(fn(string $operation) => $operation === 'edit')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->hiddenOn('create')
                            ->dehydrated()
                            ->columnSpanFull(),

                        Placeholder::make('pilih_jadwal_title')
                            ->hiddenLabel()
                            ->hiddenOn('edit')
                            ->content(new HtmlString(
                                '<div style="margin-top: 8px; margin-bottom: 4px;">'
                                .'<div style="font-size:12px; font-weight:800; color:#B8956F; text-transform:uppercase; letter-spacing:.12em;">Pilih Jadwal Baru</div>'
                                .'<div style="font-size:13px; color:#64748B; line-height:1.5;">Gunakan tanggal dan slot yang tersedia. Slot penuh dan waktu yang sudah lewat tidak bisa dipilih.</div>'
                                .'</div>'
                            )),

                        ToggleButtons::make('new_tanggal')
                            ->label('Pilih Tanggal Baru')
                            ->options(fn (Get $get): array => self::dateOptions($get))
                            ->colors(fn (Get $get): array => array_fill_keys(array_keys(self::dateOptions($get)), 'warning'))
                            ->icons(fn (Get $get): array => array_fill_keys(array_keys(self::dateOptions($get)), 'heroicon-o-calendar-days'))
                            ->columns(['default' => 2, 'md' => 4])
                            ->required(fn(string $operation) => $operation === 'create')
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => [
                                $set('new_jam', null),
                                $set('tanggal_jam', null),
                            ])
                            ->dehydrated(false)
                            ->validationMessages([
                                'required' => 'Pilih tanggal terlebih dahulu.',
                            ])
                            ->hiddenOn('edit'),

                        ToggleButtons::make('new_jam')
                            ->label(fn (Get $get) => $get('jenis') === 'treatment'
                                ? 'Pilih Slot Treatment Baru (WIB)'
                                : 'Pilih Jam Baru (WIB)')
                            ->options(fn (Get $get): array => self::slotOptions($get, $get('new_tanggal')))
                            ->colors(fn (Get $get): array => array_fill_keys(array_keys(self::slotOptions($get, $get('new_tanggal'))), 'warning'))
                            ->icons(fn (Get $get): array => array_fill_keys(array_keys(self::slotOptions($get, $get('new_tanggal'))), 'heroicon-o-clock'))
                            ->disableOptionWhen(fn (string $value, Get $get): bool => self::slotDisabled($get, $get('new_tanggal'), $value))
                            ->inline(false)
                            ->columns(['default' => 1, 'md' => 2])
                            ->required(fn(string $operation) => $operation === 'create')
                            ->visible(fn (Get $get, string $operation): bool => filled($get('new_tanggal')) && $operation === 'create')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state && $get('new_tanggal')) {
                                    $start = self::slotStart($get('new_tanggal'), $state);
                                    if ($start) {
                                        $set('tanggal_jam', $start->format('Y-m-d H:i:s'));
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->validationMessages([
                                'required' => 'Pilih slot atau jam terlebih dahulu.',
                            ])
                            ->hiddenOn('edit'),
                    ]),


                // ═══════════════════════════════════════════
                // Section 3: Status dan Catatan
                // ═══════════════════════════════════════════
                Section::make('Catatan')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->label('Status Jadwal')
                            ->native(false)
                            ->placeholder('')
                            ->options([
                                'diajukan' => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'batal' => 'Dibatalkan',
                                'selesai' => 'Selesai',
                                'tidak_hadir' => 'Tidak Hadir',
                            ])
                            ->required()
                            ->default('disetujui')
                            ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->hiddenOn('create')
                            ->columnSpanFull(),

                        Textarea::make('catatan_klinik')
                            ->label('Catatan')
                            ->placeholder('Instruksi internal atau catatan khusus untuk staf klinik (Pasien tidak dapat melihat ini).')
                            ->rows(3)
                            ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->columnSpanFull(),

                        Hidden::make('dibuat_oleh_id')
                            ->default(fn () => Filament::auth()->id())
                            ->dehydrated(true),


                    ]),

                // ═══════════════════════════════════════════
                // Section 4: Lanjut ke Treatment (Konsultasi only)
                // ═══════════════════════════════════════════
                Section::make('Lanjut ke Treatment')
                    ->description('Tambahkan treatment lanjutan yang akan dikerjakan setelah konsultasi. Harga dihitung otomatis dan akan masuk ke transaksi appointment ini.')
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => $get('jenis') === 'konsultasi')
                    ->schema([
                        Toggle::make('lanjut_treatment')
                            ->label('Pasien lanjut ke treatment')
                            ->helperText('Aktifkan jika setelah konsultasi pasien langsung memerlukan treatment.')
                            ->reactive()
                            ->afterStateHydrated(function (Toggle $component, ?Appointment $record) {
                                if (! $record) return;
                                $hasFollowup = $record->items()
                                    ->whereIn('item_type', ['treatment'])
                                    ->exists();
                                $component->state($hasFollowup);
                            }),

                        Repeater::make('lanjut_items')
                            ->hiddenLabel()
                            ->visible(fn (Get $get) => (bool) $get('lanjut_treatment'))
                            ->reactive()
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Treatment')
                            ->reorderable(false)
                            ->afterStateHydrated(function (Repeater $component, ?Appointment $record) {
                                if (! $record) return;
                                $items = $record->items()
                                    ->whereIn('item_type', ['treatment'])
                                    ->get()
                                    ->map(fn ($i) => [
                                        'item_type'    => $i->item_type,
                                        'treatment_id' => $i->treatment_id,
                                        'qty'          => (int) ($i->qty ?? 1),
                                        'handled_by_type' => $i->handled_by_type,
                                        'handled_by_id'   => $i->handled_by_id,
                                    ])
                                    ->all();
                                if (! empty($items)) {
                                    $component->state($items);
                                }
                            })
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
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $handlerId = self::defaultHandlerIdForTreatment((int) $state);
                                            $set('handled_by_id', $handlerId);
                                            $set('handled_by_type', $handlerId ? self::resolveSelectedHandlerType($handlerId) : null);
                                        }),


                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('qty')
                                        ->label('Qty')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->reactive(),

                                    Hidden::make('handled_by_type')
                                        ->dehydrated(),

                                    Select::make('handled_by_id')
                                        ->label('Dokter / Terapis')
                                        ->placeholder('Pilih petugas')
                                        ->options(fn (Get $get): array => self::handlerOptionsForTreatment((int) $get('treatment_id')))
                                        ->searchable()
                                        ->native(false)
                                        ->visible(fn (Get $get) => $get('item_type') === 'treatment' && ! (Filament::auth()->user()?->hasRole('pasien') ?? false))
                                        ->required(fn (Get $get) => $get('item_type') === 'treatment' && ! (Filament::auth()->user()?->hasRole('pasien') ?? false))
                                        ->validationMessages([
                                            'required' => 'Pilih dokter/terapis yang menangani treatment ini.',
                                        ])
                                        ->disabled(fn (Get $get): bool => self::isDoctorOnlyTreatment((int) $get('treatment_id')))
                                        ->dehydrated()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, $set) => $set('handled_by_type', $state ? self::resolveSelectedHandlerType((int) $state) : null))
                                        ->helperText(fn (Get $get): ?string => self::handlerHelperText((int) $get('treatment_id'))),

                                    Placeholder::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->content(function (Get $get): HtmlString {
                                            $price = self::resolveItemPrice($get('item_type'), $get('treatment_id'));
                                            $text = $price !== null
                                                ? 'Rp ' . number_format($price, 0, ',', '.')
                                                : '—';
                                            return new HtmlString('<div style="font-weight:600; color:#111827;">'.$text.'</div>');
                                        }),

                                    Placeholder::make('subtotal')
                                        ->label('Subtotal')
                                        ->content(function (Get $get): HtmlString {
                                            $price = self::resolveItemPrice($get('item_type'), $get('treatment_id'));
                                            $qty = max(1, (int) ($get('qty') ?: 1));
                                            $text = $price !== null
                                                ? 'Rp ' . number_format($price * $qty, 0, ',', '.')
                                                : '—';
                                            return new HtmlString('<div style="font-weight:700; color:#B8956F;">'.$text.'</div>');
                                        }),
                                ]),
                            ])
                            ->columnSpanFull(),

                        Placeholder::make('lanjut_total')
                            ->label('Total Estimasi Treatment')
                            ->visible(fn (Get $get) => (bool) $get('lanjut_treatment'))
                            ->content(function (Get $get): HtmlString {
                                $items = $get('lanjut_items') ?? [];
                                $total = 0;
                                foreach ($items as $row) {
                                    $p = self::resolveItemPrice($row['item_type'] ?? null, $row['treatment_id'] ?? null);
                                    if ($p === null) continue;
                                    $total += $p * max(1, (int) ($row['qty'] ?? 1));
                                }
                                $fmt = 'Rp ' . number_format($total, 0, ',', '.');
                                return new HtmlString('
                                    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px;">
                                        <span style="color:#7A5A3A; font-size:0.875rem; font-weight:500;">Subtotal treatment akan ditagih pada transaksi appointment ini</span>
                                        <span style="color:#B8956F; font-size:1.125rem; font-weight:700; letter-spacing:-0.01em;">'.$fmt.'</span>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    private static function resolveAllowedHandlerTypes(?int $treatmentId): array
    {
        if (! $treatmentId) {
            return ['dokter', 'terapis'];
        }

        $treatment = Treatment::with('category')->find($treatmentId);

        return $treatment?->category?->allowedHandlerTypes() ?? ['dokter', 'terapis'];
    }

    private static function handlerOptionsForTreatment(?int $treatmentId): array
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

    private static function defaultHandlerIdForTreatment(?int $treatmentId): ?int
    {
        $allowed = self::resolveAllowedHandlerTypes($treatmentId);

        return $allowed === ['dokter']
            ? array_key_first(self::activeStaffOptions('dokter'))
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

    private static function dateOptions(Get $get): array
    {
        $options = [];
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $jenis = $get('jenis');
        $dokterId = $get('dokter_id');

        for ($i = 0; $i < 14; $i++) {
            $date = now()->copy()->startOfDay()->addDays($i);

            if ($jenis === 'konsultasi') {
                $hari = $mapHari[$date->dayOfWeekIso] ?? null;
                $hasSchedule = $hari && $dokterId && JadwalPraktik::query()
                    ->where('user_id', $dokterId)
                    ->where('jenis_layanan', 'konsultasi')
                    ->whereRaw('LOWER(hari) = ?', [$hari])
                    ->where('is_active', true)
                    ->exists();

                if (! $hasSchedule) {
                    continue;
                }
            }

            $options[$date->toDateString()] = strtoupper($date->locale('id')->translatedFormat('D')) . ' ' . $date->format('d') . ' ' . $date->locale('id')->translatedFormat('M');
        }

        return $options;
    }

    private static function slotOptions(Get $get, ?string $date): array
    {
        if (! $date) return [];

        return $get('jenis') === 'treatment'
            ? self::treatmentSlotOptions($get, $date)
            : self::consultationSlotOptions($get, $date);
    }

    private static function slotDisabled(Get $get, ?string $date, string $slot): bool
    {
        if (! $date || ! $slot) return true;

        $start = self::slotStart($date, $slot);
        if (! $start || $start->isPast()) return true;

        if ($get('jenis') === 'treatment') {
            $booked = Appointment::query()
                ->where('jenis', 'treatment')
                ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                ->count();
            return $booked >= 2;
        }

        return Appointment::query()
            ->where('jenis', 'konsultasi')
            ->where('dokter_id', $get('dokter_id'))
            ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
            ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
            ->exists();
    }

    private static function treatmentSlotOptions(Get $get, string $date): array
    {
        $slots = ['08:00 - 10:00', '10:30 - 12:30', '13:00 - 15:00', '15:30 - 17:30'];
        $options = [];
        foreach ($slots as $slot) {
            $start = self::slotStart($date, $slot);
            $booked = $start
                ? Appointment::query()
                    ->where('jenis', 'treatment')
                    ->where('tanggal_jam', $start->format('Y-m-d H:i:s'))
                    ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                    ->count()
                : 2;

            $sisa = max(0, 2 - $booked);
            $label = str_replace(':', '.', $slot);
            $options[$slot] = $label . ' | ' . ($start?->isPast() ? 'Lewat' : ($sisa > 0 ? "Sisa {$sisa} slot" : 'Penuh'));
        }
        return $options;
    }

    private static function consultationSlotOptions(Get $get, string $date): array
    {
        $dokterId = $get('dokter_id');
        if (! $dokterId) return [];

        $dt = Carbon::parse($date);
        $mapHari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $hari = $mapHari[$dt->dayOfWeekIso] ?? null;

        $jadwal = $hari ? JadwalPraktik::query()
            ->where('user_id', $dokterId)
            ->where('jenis_layanan', 'konsultasi')
            ->whereRaw('LOWER(hari) = ?', [$hari])
            ->where('is_active', true)
            ->first() : null;

        if (! $jadwal) return [];

        $start = Carbon::parse($date . ' ' . $jadwal->jam_mulai);
        $end = Carbon::parse($date . ' ' . $jadwal->jam_selesai);
        $duration = max(5, (int) ($jadwal->durasi_slot_menit ?: 60));
        $options = [];

        for ($cursor = $start->copy(); $cursor->lt($end); $cursor->addMinutes($duration)) {
            $value = $cursor->format('H:i');
            $isBooked = Appointment::query()
                ->where('jenis', 'konsultasi')
                ->where('dokter_id', $dokterId)
                ->where('tanggal_jam', $cursor->format('Y-m-d H:i:s'))
                ->whereIn('status', ['diajukan', 'akan_datang', 'diproses', 'disetujui'])
                ->exists();

            $options[$value] = str_replace(':', '.', $value) . ' | ' . ($cursor->isPast() ? 'Lewat' : ($isBooked ? 'Terisi' : 'Tersedia'));
        }

        return $options;
    }

    private static function slotStart(string $date, string $slot): ?Carbon
    {
        $time = trim(explode('-', $slot)[0] ?? $slot);
        $time = str_replace('.', ':', $time);
        try {
            return Carbon::parse($date . ' ' . $time);
        } catch (\Throwable) {
            return null;
        }
    }
}
