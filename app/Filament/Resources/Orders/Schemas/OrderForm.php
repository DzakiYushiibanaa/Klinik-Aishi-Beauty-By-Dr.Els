<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Appointment;
use App\Models\Bundle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderForm
{
    /**
     * Kalau dipanggil dari dalam repeater, user_id ada di '../../user_id'
     */
    private static function activePatientId(Get $get): ?int
    {
        $id = $get('../../user_id') ?? $get('user_id');
        $id = (int) ($id ?? 0);

        return $id > 0 ? $id : null;
    }

    /**
     * SET ke ROOT order meskipun dipanggil dari dalam repeater.
     * Ini kunci biar subtotal/diskon_total/grand_total selalu ke-update.
     */
    private static function setRoot(callable $set, Get $get, string $key, mixed $value): void
    {
        // kalau di dalam repeater, biasanya '../../user_id' ada
        $isInRepeater = $get('../../user_id') !== null;
        $path = $isInRepeater ? "../../{$key}" : $key;

        $set($path, $value);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Order')
                    ->description('Urutan: Pilih Pasien → Isi Item → Cek Ringkasan (Promo/Ongkir) → Atur Pembayaran.')
                    ->schema([
                        // ======================
                        // 1) IDENTITAS
                        // ======================
                        Section::make('1) Identitas Order')
                            ->columns(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Pasien')
                                    ->options(fn () => User::query()
                                        ->whereHas('roles', fn (Builder $q) => $q->where('name', 'pasien'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // ganti pasien => reset semua yang tergantung pasien
                                        $set('items', []);
                                        $set('promotion_id', null);
                                        $set('ongkir', 0);

                                        // reset ringkasan
                                        $set('subtotal', 0);
                                        $set('diskon_total', 0);
                                        $set('grand_total', 0);
                                        $set('order_type', 'campuran');
                                    }),

                                TextInput::make('kode_order')
                                    ->label('Kode Order')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Otomatis dibuat saat simpan.'),

                                Select::make('sumber')
                                    ->label('Sumber')
                                    ->options([
                                        'online' => 'Online',
                                        'offline' => 'Offline',
                                    ])
                                    ->required()
                                    ->default('online'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                        'lunas' => 'Lunas',
                                        'batal' => 'Batal',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->live(),
                            ]),

                        // ======================
                        // 2) ITEM
                        // ======================
                        Section::make('2) Item Transaksi')
                            ->description('Kalau terkait appointment (konsultasi/treatment), pilih Appointment agar deskripsi & harga otomatis.')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Item')
                                    ->relationship('items')
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Item')
                                    ->reorderable(false)
                                    ->required()
                                    ->columns(1)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set, Get $get) => self::recalcTotals($set, $get))
                                    ->schema([
                                        // Header item: tipe + appointment
                                        Grid::make(2)->schema([
                                            Select::make('item_type')
                                                ->label('Tipe Item')
                                                ->options([
                                                    'product' => 'Produk',
                                                    'konsultasi' => 'Konsultasi',
                                                    'treatment' => 'Treatment',
                                                    'bundle' => 'Bundle',
                                                    'membership' => 'Membership',
                                                ])
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    // reset field sesuai tipe
                                                    $set('product_id', null);
                                                    $set('treatment_id', null);
                                                    $set('bundle_id', null);
                                                    $set('appointment_id', null);

                                                    $set('qty', 1);
                                                    $set('diskon', 0);
                                                    $set('harga_satuan', 0);
                                                    $set('description', null);
                                                    $set('subtotal', 0);

                                                    // update ringkasan order
                                                    self::recalcTotals($set, $get);
                                                }),

                                            Select::make('appointment_id')
                                                ->label('Ambil dari Appointment (Opsional)')
                                                ->options(function (Get $get) {

                                                    $patientId = self::activePatientId($get);
                                                    if (! $patientId) return [];

                                                    $currentAppointmentId = (int) ($get('appointment_id') ?? 0);

                                                    // ✅ current order id saat edit: /orders/{record}/edit
                                                    $currentOrderId = request()->route('record'); // biasanya ini order_id
                                                    $currentOrderId = is_numeric($currentOrderId) ? (int) $currentOrderId : null;

                                                    // 🔒 appointment yang sudah dipakai di order lain (menunggu/lunas)
                                                    $usedAppointmentIds = OrderItem::query()
                                                        ->whereNotNull('appointment_id')
                                                        ->whereHas('order', function (Builder $q) use ($currentOrderId) {
                                                            $q->whereIn('status', ['menunggu_pembayaran', 'lunas']);

                                                            // ✅ exclude order yg sedang diedit biar gak invalid
                                                            if ($currentOrderId) {
                                                                $q->where('order_id', '!=', $currentOrderId);
                                                            }
                                                        })
                                                        ->pluck('appointment_id')
                                                        ->unique()
                                                        ->values()
                                                        ->all();

                                                    $appts = \App\Models\Appointment::query()
                                                        ->where('user_id', $patientId)

                                                        // sesuaikan rule kamu: mau yang selesai aja / disetujui+selesai
                                                        ->whereIn('status', ['disetujui', 'selesai'])

                                                        ->when(! empty($usedAppointmentIds), fn ($q) => $q->whereNotIn('appointment_id', $usedAppointmentIds))
                                                        ->orderByDesc('tanggal_jam')
                                                        ->limit(200)
                                                        ->get();

                                                    // ✅ kalau sedang edit dan current appointment tidak ada di list, tetap masukin
                                                    if ($currentAppointmentId && ! $appts->contains('appointment_id', $currentAppointmentId)) {
                                                        $current = \App\Models\Appointment::query()
                                                            ->where('user_id', $patientId)
                                                            ->where('appointment_id', $currentAppointmentId)
                                                            ->first();

                                                        if ($current) {
                                                            $appts->prepend($current);
                                                        }
                                                    }

                                                    return $appts->mapWithKeys(function ($a) {
                                                        $tgl = $a->tanggal_jam
                                                            ? Carbon::parse($a->tanggal_jam)->format('d/m/Y H:i')
                                                            : '-';

                                                        return [
                                                            $a->appointment_id => "APT-{$a->appointment_id} | {$tgl} | {$a->jenis} | {$a->status}",
                                                        ];
                                                    })->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->nullable()
                                                ->live()
                                                ->reactive()
                                                ->disabled(fn (Get $get) => ! self::activePatientId($get))
                                                ->helperText('Appointment yang sudah ditagih pada order lain (menunggu/lunas) tidak akan muncul.')
                                                ->visible(fn (Get $get) => in_array($get('item_type'), ['treatment', 'bundle', 'konsultasi'], true))
                                                ->rule(function (Get $get) {

                                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        if (! $value) return;

                                                        $currentOrderId = request()->route('record');
                                                        $currentOrderId = is_numeric($currentOrderId) ? (int) $currentOrderId : null;

                                                        $exists = OrderItem::query()
                                                            ->where('appointment_id', $value)
                                                            ->whereHas('order', function (Builder $q) use ($currentOrderId) {
                                                                $q->whereIn('status', ['menunggu_pembayaran', 'lunas']);

                                                                // ✅ exclude order ini sendiri pas edit
                                                                if ($currentOrderId) {
                                                                    $q->where('order_id', '!=', $currentOrderId);
                                                                }
                                                            })
                                                            ->exists();

                                                        if ($exists) {
                                                            $fail('Appointment ini sudah pernah ditagih / sudah lunas pada order lain.');
                                                        }
                                                    };
                                                })
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    if (! $state) {
                                                        // kalau appointment dilepas, item kembali manual
                                                        self::recalcTotals($set, $get);
                                                        return;
                                                    }

                                                    $appt = Appointment::query()
                                                        ->with(['dokter', 'terapis', 'treatment', 'items.treatment'])
                                                        ->find($state);

                                                    if (! $appt) return;

                                                    $tgl = $appt->tanggal_jam
                                                        ? Carbon::parse($appt->tanggal_jam)->format('d/m/Y H:i')
                                                        : '-';

                                                    // kunci supaya tidak campur manual select
                                                    $set('product_id', null);
                                                    $set('bundle_id', null);
                                                    $set('treatment_id', null);
                                                    $set('diskon', 0);
                                                    $set('qty', 1);

                                                    // ===== KONSULTASI =====
                                                    if ($appt->jenis === 'konsultasi') {
                                                        $set('item_type', 'konsultasi');

                                                        $dokterName = $appt->dokter?->name ?? 'Dokter';
                                                        $set('description', "Konsultasi dengan {$dokterName} ({$tgl})");

                                                        $hargaKonsul = 0; // TODO isi harga konsultasi kamu
                                                        $set('harga_satuan', (int) $hargaKonsul);
                                                        $set('subtotal', self::calcItemSubtotal(1, (int) $hargaKonsul, 0));

                                                        self::recalcTotals($set, $get);
                                                        return;
                                                    }

                                                    // ===== TREATMENT =====
                                                    if ($appt->jenis === 'treatment') {
                                                        $set('item_type', 'treatment');

                                                        // multi appointment_items
                                                        $items = $appt->items ?? collect();
                                                        if ($items->count() > 0) {
                                                            $total = 0;
                                                            $lines = [];

                                                            foreach ($items as $it) {
                                                                $qty = (int) ($it->qty ?? 1);
                                                                $nama = $it->treatment?->nama ?? 'Treatment';
                                                                $harga = (int) ($it->treatment?->harga ?? 0);

                                                                $total += ($harga * $qty);
                                                                $lines[] = $qty > 1 ? "{$nama} x{$qty}" : $nama;
                                                            }

                                                            // dibuat single-line biar simpel (qty=1)
                                                            $set('description', 'Treatment: ' . implode(', ', $lines) . " ({$tgl})");
                                                            $set('harga_satuan', $total);
                                                            $set('subtotal', self::calcItemSubtotal(1, $total, 0));

                                                            self::recalcTotals($set, $get);
                                                            return;
                                                        }

                                                        // fallback single treatment_id
                                                        if (! empty($appt->treatment_id) && $appt->treatment) {
                                                            $nama = $appt->treatment->nama ?? 'Treatment';
                                                            $harga = (int) ($appt->treatment->harga ?? 0);

                                                            $set('description', "{$nama} ({$tgl})");
                                                            $set('harga_satuan', $harga);
                                                            $set('subtotal', self::calcItemSubtotal(1, $harga, 0));

                                                            self::recalcTotals($set, $get);
                                                            return;
                                                        }

                                                        $set('description', "Treatment (APT-{$appt->appointment_id}) ({$tgl})");
                                                        $set('harga_satuan', 0);
                                                        $set('subtotal', 0);

                                                        self::recalcTotals($set, $get);
                                                    }
                                                }),
                                        ]),

                                        // Pilihan manual (dimatiin kalau appointment dipilih)
                                        Grid::make(3)->schema([
                                            Select::make('product_id')
                                                ->label('Produk')
                                                ->options(fn () => Product::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('nama')
                                                    ->pluck('nama', 'product_id')
                                                    ->toArray()
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->visible(fn (Get $get) => $get('item_type') === 'product')
                                                ->required(fn (Get $get) => $get('item_type') === 'product')
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id'))
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    if (! $state) return;
                                                    $p = Product::find($state);
                                                    if (! $p) return;

                                                    $set('description', $p->nama);
                                                    $set('harga_satuan', (int) $p->harga);

                                                    $qty = (int) ($get('qty') ?? 1);
                                                    $diskon = (int) ($get('diskon') ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, (int) $p->harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),

                                            Select::make('treatment_id')
                                                ->label('Treatment (Manual)')
                                                ->options(fn () => Treatment::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('nama')
                                                    ->pluck('nama', 'treatment_id')
                                                    ->toArray()
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->visible(fn (Get $get) => $get('item_type') === 'treatment' && ! $get('appointment_id'))
                                                ->required(fn (Get $get) => $get('item_type') === 'treatment' && ! $get('appointment_id'))
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id'))
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    if (! $state) return;
                                                    $t = Treatment::find($state);
                                                    if (! $t) return;

                                                    $set('description', $t->nama);
                                                    $set('harga_satuan', (int) $t->harga);

                                                    $qty = (int) ($get('qty') ?? 1);
                                                    $diskon = (int) ($get('diskon') ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, (int) $t->harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),

                                            Select::make('bundle_id')
                                                ->label('Bundle (Manual)')
                                                ->options(fn () => Bundle::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('nama')
                                                    ->pluck('nama', 'bundle_id')
                                                    ->toArray()
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->visible(fn (Get $get) => $get('item_type') === 'bundle' && ! $get('appointment_id'))
                                                ->required(fn (Get $get) => $get('item_type') === 'bundle' && ! $get('appointment_id'))
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id'))
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    if (! $state) return;
                                                    $b = Bundle::find($state);
                                                    if (! $b) return;

                                                    $set('description', 'Bundle: ' . $b->nama);

                                                    $harga = (int) ($b->harga_promo ?? $b->harga_normal ?? 0);
                                                    $set('harga_satuan', $harga);

                                                    $qty = (int) ($get('qty') ?? 1);
                                                    $diskon = (int) ($get('diskon') ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, $harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),
                                        ]),

                                        TextInput::make('description')
                                            ->label('Deskripsi')
                                            ->nullable()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Grid::make(4)->schema([
                                            TextInput::make('qty')
                                                ->label('Qty')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->required()
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id')) // kalau dari appointment, dibikin simple
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    $qty = (int) ($state ?? 1);
                                                    $harga = (int) ($get('harga_satuan') ?? 0);
                                                    $diskon = (int) ($get('diskon') ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, $harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),

                                            TextInput::make('harga_satuan')
                                                ->label('Harga')
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id'))
                                                ->dehydrated(true)
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    $qty = (int) ($get('qty') ?? 1);
                                                    $harga = (int) ($state ?? 0);
                                                    $diskon = (int) ($get('diskon') ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, $harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),

                                            TextInput::make('diskon')
                                                ->label('Diskon Item')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->disabled(fn (Get $get) => (bool) $get('appointment_id'))
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, Get $get) {
                                                    $qty = (int) ($get('qty') ?? 1);
                                                    $harga = (int) ($get('harga_satuan') ?? 0);
                                                    $diskon = (int) ($state ?? 0);

                                                    $set('subtotal', self::calcItemSubtotal($qty, $harga, $diskon));
                                                    self::recalcTotals($set, $get);
                                                }),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal Item')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated(true)
                                                ->default(0),
                                        ]),
                                    ]),
                            ]),

                        // ======================
                        // 3) RINGKASAN (HASIL AKHIR)
                        // ======================
                        Section::make('3) Ringkasan')
                            ->description('Bagian ini harus langsung berubah saat item/promo/ongkir diubah.')
                            ->columns(2)
                            ->schema([
                                Select::make('promotion_id')
                                    ->label('Promo (Opsional)')
                                    ->options(fn () => Promotion::query()
                                        ->where('is_active', true)
                                        ->orderBy('nama')
                                        ->pluck('nama', 'promotion_id')
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set, Get $get) => self::recalcTotals($set, $get)),

                                TextInput::make('ongkir')
                                    ->label('Ongkir')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set, Get $get) => self::recalcTotals($set, $get)),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),

                                TextInput::make('diskon_total')
                                    ->label('Diskon Promo')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),

                                TextInput::make('grand_total')
                                    ->label('Total Bayar')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),

                                TextInput::make('order_type')
                                    ->label('Tipe Order')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default('campuran'),
                            ]),

                        // ======================
                        // 4) PEMBAYARAN
                        // ======================
                        Section::make('4) Pembayaran')
                            ->columns(2)
                            ->visible(fn (Get $get) => in_array($get('status'), ['menunggu_pembayaran', 'lunas'], true))
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Metode Bayar')
                                    ->options([
                                        'cash' => 'Cash',
                                        'midtrans' => 'Midtrans',
                                    ])
                                    ->nullable(),

                                TextInput::make('payment_channel')
                                    ->label('Channel')
                                    ->placeholder('qris / bca_va / gopay / dll')
                                    ->nullable(),

                                TextInput::make('payment_ref')
                                    ->label('Ref Pembayaran')
                                    ->placeholder('No VA / Ref Midtrans / dll')
                                    ->nullable(),

                                DateTimePicker::make('paid_at')
                                    ->label('Waktu Lunas')
                                    ->native(false)
                                    ->seconds(false)
                                    ->nullable()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->visible(fn (Get $get) => $get('status') === 'lunas'),
                            ]),
                    ])
                    ->columns(1),
            ])
            ->columns(1)
            ->reactive();
    }

    public static function calcItemSubtotal(int $qty, int $hargaSatuan, int $diskon): int
    {
        $subtotal = ($qty * $hargaSatuan) - $diskon;
        return max(0, $subtotal);
    }

    public static function recalcTotals(callable $set, Get $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $it) {
            $subtotal += (int) ($it['subtotal'] ?? 0);
        }

        $promoId = $get('promotion_id');
        $diskonTotal = self::calcPromoDiscount($promoId, $subtotal);

        $ongkir = (int) ($get('ongkir') ?? 0);
        $grandTotal = max(0, ($subtotal - $diskonTotal) + $ongkir);

        $orderType = self::inferOrderType($items);

        // ✅ ini kunci biar update-nya bukan “nyangkut” di scope repeater
        self::setRoot($set, $get, 'subtotal', $subtotal);
        self::setRoot($set, $get, 'diskon_total', $diskonTotal);
        self::setRoot($set, $get, 'grand_total', $grandTotal);
        self::setRoot($set, $get, 'order_type', $orderType);
    }

    public static function calcPromoDiscount($promoId, int $subtotal): int
    {
        if (! $promoId) return 0;

        $promo = Promotion::find($promoId);
        if (! $promo || ! $promo->is_active) return 0;

        $today = now()->startOfDay();

        if ($promo->start_date && $today->lt(Carbon::parse($promo->start_date)->startOfDay())) return 0;
        if ($promo->end_date && $today->gt(Carbon::parse($promo->end_date)->endOfDay())) return 0;

        if ((int) ($promo->min_subtotal ?? 0) > $subtotal) return 0;

        $diskon = 0;

        if (! empty($promo->diskon_nominal)) {
            $diskon = (int) $promo->diskon_nominal;
        } elseif (! empty($promo->diskon_persen)) {
            $diskon = (int) floor($subtotal * (((int) $promo->diskon_persen) / 100));
        }

        return max(0, min($diskon, $subtotal));
    }

    public static function inferOrderType(array $items): string
    {
        $types = collect($items)->pluck('item_type')->filter()->unique()->values()->all();

        if (count($types) === 1) {
            return match ($types[0]) {
                'product' => 'produk',
                'treatment' => 'treatment',
                'bundle' => 'bundle',
                'konsultasi' => 'konsultasi',
                'membership' => 'membership',
                default => 'campuran',
            };
        }

        return 'campuran';
    }
    public static function applyTotalsFromData(array $data): array
    {
        $items = $data['items'] ?? [];

        // pastiin subtotal item kebentuk walau user ga trigger live event
        foreach ($items as $i => $it) {
            $qty = (int) ($it['qty'] ?? 1);
            $harga = (int) ($it['harga_satuan'] ?? 0);
            $diskon = (int) ($it['diskon'] ?? 0);

            $items[$i]['subtotal'] = self::calcItemSubtotal($qty, $harga, $diskon);
        }

        $subtotal = array_sum(array_map(fn ($it) => (int) ($it['subtotal'] ?? 0), $items));
        $diskonTotal = self::calcPromoDiscount($data['promotion_id'] ?? null, $subtotal);

        $ongkir = (int) ($data['ongkir'] ?? 0);
        $grandTotal = max(0, ($subtotal - $diskonTotal) + $ongkir);

        $data['items'] = $items;
        $data['subtotal'] = $subtotal;
        $data['diskon_total'] = $diskonTotal;
        $data['grand_total'] = $grandTotal;

        // kalau status lunas, isi paid_at kalau kosong
        if (($data['status'] ?? null) === 'lunas' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }
}

