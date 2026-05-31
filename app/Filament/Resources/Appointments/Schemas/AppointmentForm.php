<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Bundle;
use App\Models\Consultation;
use App\Models\JadwalPraktik;
use App\Models\Treatment;
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
    public static function resolveItemPrice(?string $type, $treatmentId, $bundleId): ?int
    {
        if ($type === 'treatment' && $treatmentId) {
            $harga = Treatment::query()->whereKey($treatmentId)->value('harga');
            return $harga !== null ? (int) $harga : null;
        }

        if ($type === 'bundle' && $bundleId) {
            $b = Bundle::query()->whereKey($bundleId)->first(['harga_normal', 'harga_promo']);
            if (! $b) return null;
            return (int) ($b->harga_promo ?: $b->harga_normal);
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
                return ($type === 'treatment' && ! empty($row['treatment_id']))
                    || ($type === 'bundle' && ! empty($row['bundle_id']));
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
        $record->items()->whereIn('item_type', ['treatment', 'bundle'])->delete();

        if (! $pending || ! $pending['enabled']) {
            return;
        }

        foreach ($pending['items'] as $row) {
            $type = $row['item_type'];
            $record->items()->create([
                'item_type'    => $type,
                'treatment_id' => $type === 'treatment' ? (int) $row['treatment_id'] : null,
                'bundle_id'    => $type === 'bundle' ? (int) $row['bundle_id'] : null,
                'qty'          => max(1, (int) ($row['qty'] ?? 1)),
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
                Section::make('Identitas & Jadwal')
                    ->columnSpanFull()
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
                                TextInput::make('email')->email()->label('Email (Opsional)'),
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

                        DateTimePicker::make('tanggal_jam')
                            ->label('Tanggal & Jam')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->reactive()
                            ->helperText('Pilih jadwal sesuai jam praktik dan slot yang tersedia.')
                            ->columnSpanFull()
                                ->rule(function (Get $get, ?Appointment $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        if (! $value) return;

                                        $dt = Carbon::parse($value);

                                        if ($record && $record->tanggal_jam && $dt->equalTo(Carbon::parse($record->tanggal_jam))) {
                                            return;
                                        }

                                        if ($dt->lt(now())) {
                                            $fail('Tanggal dan Jam tidak boleh di masa lalu.');
                                            return;
                                        }

                                        $jenis = $get('jenis');
                                        if (! $jenis) return;

                                        $petugasId = $jenis === 'treatment' ? $get('terapis_id') : $get('dokter_id');
                                        if (! $petugasId) return;

                                        $mapHari = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
                                        $hari = $mapHari[$dt->dayOfWeekIso] ?? null;
                                        if (! $hari) return;

                                        $jenisLayanan = $jenis === 'treatment' ? 'treatment' : 'konsultasi';

                                        $jadwal = JadwalPraktik::query()
                                            ->where('user_id', $petugasId)
                                            ->where('jenis_layanan', $jenisLayanan)
                                            ->whereRaw('LOWER(hari) = ?', [$hari])
                                            ->where('is_active', true)
                                            ->first();

                                        if (! $jadwal) {
                                            $fail('Tidak ada jadwal praktik pada hari tersebut.');
                                            return;
                                        }

                                        $mulai   = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_mulai);
                                        $selesai = Carbon::parse($dt->format('Y-m-d') . ' ' . $jadwal->jam_selesai);

                                        if ($dt->lt($mulai) || $dt->gte($selesai)) {
                                            $fail('Jam yang dipilih di luar jam praktik.');
                                            return;
                                        }

                                        $durasi = (int) ($jadwal->durasi_slot_menit ?: 60);
                                        $diffMenit = $mulai->diffInMinutes($dt);
                                        if ($diffMenit % $durasi !== 0) {
                                            $fail('Jam harus sesuai slot jadwal praktik.');
                                            return;
                                        }

                                        $exists = Appointment::query()
                                            ->when($record, fn ($q) => $q->where('appointment_id', '!=', $record->appointment_id))
                                            ->where('jenis', $jenis)
                                            ->where('tanggal_jam', $dt->format('Y-m-d H:i:s'))
                                            ->whereIn('status', ['diajukan', 'disetujui'])
                                            ->when(
                                                $jenis === 'treatment',
                                                fn ($q) => $q->where('terapis_id', $petugasId),
                                                fn ($q) => $q->where('dokter_id', $petugasId),
                                            )
                                            ->exists();

                                        if ($exists) {
                                            $fail('Slot sudah terpakai.');
                                        }
                                    };
                                }),
                    ]),

                // ═══════════════════════════════════════════
                // Section 2: Jenis Layanan
                // ═══════════════════════════════════════════
                Section::make('Jenis Layanan')
                    ->columnSpanFull()
                    ->schema([
                        ToggleButtons::make('jenis')
                            ->hiddenLabel()
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
                            }),

                        // ── Konsultasi: Pilih Konsultasi + Dokter ──
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
                            // Hydrate from the linked appointment_items row when editing.
                            // Value is stripped from saved data via mutateFormDataUsing on Create/Edit
                            // actions and persisted separately via AppointmentForm::syncConsultationItem().
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
                            ->afterStateUpdated(fn ($state, $set) => $set('tanggal_jam', null)),

                        // ── Treatment: Pilih Treatment & Terapis side by side ──
                        Grid::make(2)->schema([
                            Select::make('terapis_id')
                                ->label('Pilih Terapis')
                                ->relationship(
                                    name: 'terapis',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) =>
                                        $query->role('terapis')
                                            ->whereHas('terapisProfile', fn (Builder $q) =>
                                                $q->whereIn('status_kerja', ['aktif', 'Aktif'])
                                            )
                                )
                                ->searchable()
                                ->preload()
                                ->required(fn (Get $get) => $get('jenis') === 'treatment')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, $set) => $set('tanggal_jam', null)),
                        ])->visible(fn (Get $get) => $get('jenis') === 'treatment'),

                        Placeholder::make('info_dokter')
                            ->hiddenLabel()
                            ->content(new HtmlString('<div style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: #D97706; background-color: #FFFBEB; padding: 12px; border-radius: 8px; border: 1px solid #FDE68A;"><svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; flex-shrink: 0; color: #F59E0B;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Field <strong>Dokter</strong> disembunyikan. Silakan ubah jenis layanan ke <strong>Konsultasi</strong> jika membutuhkan jadwal Dokter.</span></div>'))
                            ->visible(fn (Get $get) => $get('jenis') === 'treatment'),

                        Repeater::make('items')
                            ->label('Daftar Treatment / Bundle')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('item_type')
                                        ->label('Tipe')
                                        ->options([
                                            'treatment' => 'Treatment',
                                            'bundle' => 'Bundle',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, $set) => [
                                            $set('treatment_id', null),
                                            $set('bundle_id', null),
                                        ]),

                                    Select::make('treatment_id')
                                        ->label('Treatment')
                                        ->relationship('treatment', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get) => $get('item_type') === 'treatment')
                                        ->required(fn (Get $get) => $get('item_type') === 'treatment')
                                        ->reactive(),

                                    Select::make('bundle_id')
                                        ->label('Bundle')
                                        ->relationship('bundle', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get) => $get('item_type') === 'bundle')
                                        ->required(fn (Get $get) => $get('item_type') === 'bundle')
                                        ->reactive(),
                                ]),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
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
                // Section 3: Status dan Catatan
                // ═══════════════════════════════════════════
                Section::make('Status & Catatan')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->label('Status Jadwal')
                            ->native(false)
                            ->placeholder('')
                            ->options([
                                'diajukan' => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'batal' => 'Batal',
                                'selesai' => 'Selesai',
                                'tidak_hadir' => 'Tidak Hadir',
                            ])
                            ->required()
                            ->default('diajukan')
                            ->disabled(fn () => (Filament::auth()->user()?->hasRole('pasien') ?? false))
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

                        Hidden::make('sumber')
                            ->default('offline')
                            ->dehydrated(true),
                    ]),

                // ═══════════════════════════════════════════
                // Section 4: Lanjut ke Treatment (Konsultasi only)
                // ═══════════════════════════════════════════
                Section::make('Lanjut ke Treatment')
                    ->description('Tambahkan treatment/bundle lanjutan yang akan dikerjakan setelah konsultasi. Harga dihitung otomatis dan akan masuk ke transaksi appointment ini.')
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
                                    ->whereIn('item_type', ['treatment', 'bundle'])
                                    ->exists();
                                $component->state($hasFollowup);
                            }),

                        Repeater::make('lanjut_items')
                            ->hiddenLabel()
                            ->visible(fn (Get $get) => (bool) $get('lanjut_treatment'))
                            ->reactive()
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Treatment / Bundle')
                            ->reorderable(false)
                            ->afterStateHydrated(function (Repeater $component, ?Appointment $record) {
                                if (! $record) return;
                                $items = $record->items()
                                    ->whereIn('item_type', ['treatment', 'bundle'])
                                    ->get()
                                    ->map(fn ($i) => [
                                        'item_type'    => $i->item_type,
                                        'treatment_id' => $i->treatment_id,
                                        'bundle_id'    => $i->bundle_id,
                                        'qty'          => (int) ($i->qty ?? 1),
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
                                            'bundle'    => 'Bundle',
                                        ])
                                        ->default('treatment')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, $set) => [
                                            $set('treatment_id', null),
                                            $set('bundle_id', null),
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

                                    Select::make('bundle_id')
                                        ->label('Bundle')
                                        ->options(fn () => \App\Models\Bundle::query()
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
                                        ->reactive(),
                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('qty')
                                        ->label('Qty')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->reactive(),

                                    Placeholder::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->content(function (Get $get): HtmlString {
                                            $price = self::resolveItemPrice($get('item_type'), $get('treatment_id'), $get('bundle_id'));
                                            $text = $price !== null
                                                ? 'Rp ' . number_format($price, 0, ',', '.')
                                                : '—';
                                            return new HtmlString('<div style="font-weight:600; color:#111827;">'.$text.'</div>');
                                        }),

                                    Placeholder::make('subtotal')
                                        ->label('Subtotal')
                                        ->content(function (Get $get): HtmlString {
                                            $price = self::resolveItemPrice($get('item_type'), $get('treatment_id'), $get('bundle_id'));
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
                                    $p = self::resolveItemPrice($row['item_type'] ?? null, $row['treatment_id'] ?? null, $row['bundle_id'] ?? null);
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
}
