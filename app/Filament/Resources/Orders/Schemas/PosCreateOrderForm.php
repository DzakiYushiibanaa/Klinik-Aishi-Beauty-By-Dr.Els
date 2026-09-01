<?php

namespace App\Filament\Resources\Orders\Schemas;


use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Treatment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PosCreateOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                self::leftColumn(),
                self::rightColumn(),
            ]);
    }

    // ═══════════════════════════════════════════════════════
    // LEFT COLUMN — Info Pelanggan + Keranjang
    // ═══════════════════════════════════════════════════════
    private static function leftColumn(): Group
    {
        return Group::make()
            ->columnSpan(['default' => 12, 'md' => 8])
            ->schema([
                self::sectionInfoPelanggan(),
                self::sectionKeranjangBelanja(),
            ]);
    }

    private static function sectionInfoPelanggan(): Section
    {
        return Section::make('Info Pelanggan')
            ->headerActions([
                Action::make('invoice_preview')
                    ->label(fn (Get $get) => 'Invoice: ' . ($get('kode_order') ?: self::generateKode()))
                    ->color('gray')
                    ->badge()
                    ->disabled(),
            ])
            ->schema([
                Select::make('user_id')
                    ->label('Pilih Pasien')
                    ->placeholder('Cari pasien berdasarkan nama...')
                    ->options(fn () => User::query()
                        ->whereHas('roles', fn (Builder $q) => $q->where('name', 'pasien'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->createOptionForm(self::newPatientFormSchema())
                    ->createOptionModalHeading('Tambah Pasien Baru')
                    ->createOptionAction(fn ($action) => $action
                        ->modalWidth('lg')
                        ->modalSubmitActionLabel('Simpan Pasien')
                        ->modalCancelActionLabel('Batal')
                        ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End))
                    ->createOptionUsing(fn (array $data) => self::createNewPatient($data))
                    ->columnSpanFull(),

                Hidden::make('kode_order')
                    ->dehydrated(true)
                    ->default(fn () => self::generateKode()),
            ]);
    }

    /**
     * Schema for the "Tambah Pasien Baru" inline form.
     */
    private static function newPatientFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->placeholder('Masukkan nama lengkap')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('phone')
                    ->label('No. Telepon')
                    ->placeholder('08xxx-xxxx-xxxx')
                    ->tel()
                    ->maxLength(20)
                    ->columnSpanFull(),

                \Filament\Forms\Components\DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->native(false)
                    ->placeholder(''),
            ]),

            \Filament\Forms\Components\Toggle::make('is_member')
                ->label('Daftarkan sebagai Member?')
                ->helperText('Member akan mendapat kode RM, non-member pakai NM.')
                ->default(false),
        ];
    }

    /**
     * Create a new pasien + user account (pasien role).
     * Returns the new user ID to populate the Select.
     *
     * Walk-in POS patients are registered with only a name. Email & password
     * are optional (null) since they don't need to login.
     */
    private static function createNewPatient(array $data): int
    {
        $user = User::create([
            'name'          => $data['name'],
            'phone'         => $data['phone'] ?? null,
        ]);

        $user->assignRole('pasien');

        $isMember = (bool) ($data['is_member'] ?? false);
        $prefix = $isMember ? 'RM' : 'NM';
        $noRekamMedis = \App\Support\CodeGenerator::prefixNumber(
            new \App\Models\Pasien(),
            'no_rekam_medis',
            $prefix,
            6,
            '-'
        );

        \App\Models\Pasien::create([
            'user_id'        => $user->id,
            'nama'           => $data['name'],
            'no_telepon'     => $data['phone'] ?? null,
            'tanggal_lahir'  => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin'  => $data['jenis_kelamin'] ?? null,
            'no_rekam_medis' => $noRekamMedis,
            'is_member'      => $isMember,
            'member_since'   => $isMember ? now() : null,
        ]);

        return $user->id;
    }

    private static function sectionKeranjangBelanja(): Section
    {
        return Section::make('Keranjang Belanja')
            ->schema([
                Repeater::make('items')
                    ->hiddenLabel()
                    ->relationship('items')
                    ->defaultItems(1)
                    ->addActionLabel('+ Tambah Item')
                    ->reorderable(false)
                    ->columns(12)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcTotals($set, $get))
                    ->deleteAction(
                        fn ($action) => $action->after(fn (Set $set, Get $get) => self::recalcTotals($set, $get))
                    )
                    ->schema([
                        // Row 1: Tipe + Item (cascading dropdowns)
                        Select::make('item_type')
                            ->label('Tipe')
                            ->options([
                                'product'   => 'Produk',
                                'treatment' => 'Treatment',
                            ])
                            ->required()
                            ->live()
                            ->default('product')
                            ->columnSpan(['default' => 12, 'md' => 4])
                            ->afterStateUpdated(function (Set $set) {
                                $set('produk_layanan_id', null);
                                $set('product_id', null);
                                $set('treatment_id', null);
                                $set('description', null);
                                $set('harga_satuan', 0);
                                $set('subtotal', 0);
                            }),

                        Select::make('produk_layanan_id')
                            ->label(fn (Get $get) => match ($get('item_type')) {
                                'treatment' => 'Pilih Treatment',
                                default     => 'Pilih Produk',
                            })
                            ->placeholder('Pilih dari daftar...')
                            ->columnSpan(['default' => 12, 'md' => 8])
                            ->options(fn (Get $get) => self::itemOptionsByType($get('item_type')))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) =>
                                self::handleItemSelection($state, $set, $get)
                            ),

                        // Row 2: Qty + Harga Satuan
                        TextInput::make('qty')
                            ->label('Qty')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->columnSpan(['default' => 6, 'md' => 4])
                            ->live(debounce: 300)
                            ->afterStateUpdated(fn (Set $set, Get $get) =>
                                self::recalcItemSubtotal($set, $get)
                            ),

                        TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(['default' => 6, 'md' => 8]),

                        // Row 3: Diskon + Subtotal
                        TextInput::make('diskon')
                            ->label('Diskon (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->columnSpan(['default' => 12, 'md' => 6])
                            ->live(debounce: 300)
                            ->afterStateUpdated(fn (Set $set, Get $get) =>
                                self::recalcItemSubtotal($set, $get)
                            ),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(['default' => 12, 'md' => 6])
                            ->extraInputAttributes(['style' => 'font-weight:600; color:#111827;']),

                        Hidden::make('product_id')->dehydrated(true),
                        Hidden::make('treatment_id')->dehydrated(true),
                        Hidden::make('description')->dehydrated(true),
                    ]),
            ]);
    }

    // ═══════════════════════════════════════════════════════
    // RIGHT COLUMN — Ringkasan & Pembayaran (sticky)
    // ═══════════════════════════════════════════════════════
    private static function rightColumn(): Group
    {
        return Group::make()
            ->columnSpan(['default' => 12, 'md' => 4])
            ->extraAttributes(['class' => 'fi-pos-summary-wrapper'])
            ->schema([
                Section::make('Ringkasan & Pembayaran')
                    ->extraAttributes(['class' => 'fi-pos-summary'])
                    ->schema([
                        self::promoCodeRow(),
                        Hidden::make('promotion_id'),
                        self::paymentMethodSelect(),
                        self::cashInput(),
                        self::summaryDisplay(),
                        Hidden::make('subtotal')->default(0)->dehydrated(true),
                        Hidden::make('diskon_total')->default(0)->dehydrated(true),
                        Hidden::make('grand_total')->default(0)->dehydrated(true),
                        Hidden::make('ongkir')->default(0)->dehydrated(true),
                        self::submitButton(),
                    ]),
            ]);
    }

    private static function promoCodeRow(): Grid
    {
        return Grid::make(12)->schema([
            TextInput::make('promo_code')
                ->label(new HtmlString('Kode Promo <span style="color:#8A8078; font-weight:400; font-size:0.75rem;">(Optional)</span>'))
                ->placeholder('Masukkan kode promo')
                ->dehydrated(false)
                ->columnSpan(8),

            Actions::make([
                Action::make('apply_promo')
                    ->label('Pakai')
                    ->icon('heroicon-o-ticket')
                    ->color('primary')
                    ->action(fn (Set $set, Get $get) => self::applyPromo($set, $get)),
            ])
                ->alignEnd()
                ->columnSpan(4)
                ->extraAttributes(['class' => 'fi-pos-promo-apply']),
        ]);
    }

    private static function paymentMethodSelect(): Select
    {
        return Select::make('payment_method')
            ->label('Metode Pembayaran')
            ->placeholder('Pilih metode pembayaran')
            ->options([
                'cash' => 'Cash',
                'bsi'  => 'BSI',
                'bca'  => 'BCA',
                'bri'  => 'BRI',
            ])
            ->required()
            ->live();
    }

    private static function cashInput(): TextInput
    {
        return TextInput::make('cash_received')
            ->label('Nominal Tunai Pelanggan')
            ->placeholder('0')
            ->numeric()
            ->prefix('Rp')
            ->default(0)
            ->dehydrated(fn (Get $get) => $get('payment_method') === 'cash')
            ->live(debounce: 300)
            ->visible(fn (Get $get) => $get('payment_method') === 'cash');
    }

    private static function summaryDisplay(): Placeholder
    {
        return Placeholder::make('summary_display')
            ->hiddenLabel()
            ->content(fn (Get $get): HtmlString => self::renderSummaryHtml($get));
    }

    private static function submitButton(): Actions
    {
        return Actions::make([
            Action::make('submit_order')
                ->label('Terima Pembayaran')
                ->icon('heroicon-o-wallet')
                ->color('primary')
                ->size(\Filament\Support\Enums\Size::Large)
                ->extraAttributes(['class' => 'fi-pos-submit-btn w-full'])
                ->action(fn ($livewire) => $livewire->create()),
        ])->fullWidth();
    }

    // ═══════════════════════════════════════════════════════
    // ITEM HANDLERS
    // ═══════════════════════════════════════════════════════

    /**
     * Return options for the selected type (product / treatment).
     * Keys are numeric IDs — no composite strings, fully compatible with Filament.
     */
    private static function itemOptionsByType(?string $type): array
    {
        return match ($type) {
            'product' => Product::where('is_active', true)
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn ($p) => [
                    $p->product_id => $p->nama . ' — Rp ' . number_format((int) $p->harga, 0, ',', '.'),
                ])
                ->toArray(),

            'treatment' => Treatment::where('is_active', true)
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn ($t) => [
                    $t->treatment_id => $t->nama . ' — Rp ' . number_format((int) $t->harga, 0, ',', '.'),
                ])
                ->toArray(),



            default => [],
        };
    }

    /**
     * Handle selection of a product / treatment.
     * Populates ID, description, harga_satuan, subtotal based on the active item_type.
     */
    private static function handleItemSelection(?int $id, Set $set, Get $get): void
    {
        if (!$id) return;

        $type = $get('item_type') ?? 'product';

        // Reset all type-specific IDs first
        $set('product_id', null);
        $set('treatment_id', null);

        [$price, $description] = match ($type) {
            'product'   => self::resolveProduct((string) $id, $set),
            'treatment' => self::resolveTreatment((string) $id, $set),
            default     => [0, ''],
        };

        $set('description', $description);
        $set('harga_satuan', $price);

        self::recalcItemSubtotal($set, $get);
    }

    /** @return array{0:int,1:string} [price, description] */
    private static function resolveProduct(string $id, Set $set): array
    {
        $p = Product::find($id);
        if (!$p) return [0, ''];
        $set('product_id', $p->product_id);
        return [(int) $p->harga, $p->nama];
    }

    /** @return array{0:int,1:string} */
    private static function resolveTreatment(string $id, Set $set): array
    {
        $t = Treatment::find($id);
        if (!$t) return [0, ''];
        $set('treatment_id', $t->treatment_id);
        return [(int) $t->harga, $t->nama];
    }



    /**
     * Recalc subtotal for a single cart item (called inside repeater context).
     * Formula: (qty * harga_satuan) - diskon, minimum 0.
     * Also triggers root-level totals recalc.
     */
    private static function recalcItemSubtotal(Set $set, Get $get): void
    {
        $qty    = max(1, (int) ($get('qty') ?? 1));
        $harga  = max(0, (int) ($get('harga_satuan') ?? 0));
        $diskon = max(0, (int) ($get('diskon') ?? 0));

        $subtotal = max(0, ($qty * $harga) - $diskon);
        $set('subtotal', $subtotal);

        // Walk up to root (../../) and recalc totals
        self::recalcTotalsFromNested($set, $get);
    }

    // ═══════════════════════════════════════════════════════
    // TOTALS CALCULATION
    // ═══════════════════════════════════════════════════════

    /**
     * Recalc totals from ROOT form context (not inside repeater).
     */
    private static function recalcTotals(Set $set, Get $get): void
    {
        $items = $get('items') ?? [];
        self::applyTotals($set, $get, $items, isNested: false);
    }

    /**
     * Recalc totals from INSIDE a repeater row — walks up via `../../items`.
     */
    private static function recalcTotalsFromNested(Set $set, Get $get): void
    {
        $items = $get('../../items') ?? [];
        self::applyTotals($set, $get, $items, isNested: true);
    }

    /**
     * Apply subtotal/diskon/grand_total to the correct form scope.
     */
    private static function applyTotals(Set $set, Get $get, array $items, bool $isNested): void
    {
        $subtotal = self::sumItemSubtotals($items);

        $promoId = $isNested ? $get('../../promotion_id') : $get('promotion_id');
        $diskon = self::calcPromoDiscount((int) $promoId, $subtotal);

        $grandTotal = max(0, $subtotal - $diskon);

        $prefix = $isNested ? '../../' : '';
        $set($prefix . 'subtotal', $subtotal);
        $set($prefix . 'diskon_total', $diskon);
        $set($prefix . 'grand_total', $grandTotal);
    }

    private static function sumItemSubtotals(array $items): int
    {
        return array_reduce($items, fn ($carry, $item) => $carry + (int) ($item['subtotal'] ?? 0), 0);
    }

    private static function calcPromoDiscount(int $promoId, int $subtotal): int
    {
        if (!$promoId || $subtotal <= 0) {
            return 0;
        }

        $promo = Promotion::find($promoId);
        if (!$promo || !$promo->is_active) {
            return 0;
        }

        if ((int) ($promo->min_subtotal ?? 0) > $subtotal) {
            return 0;
        }

        $discount = match (true) {
            !empty($promo->diskon_nominal) => (int) $promo->diskon_nominal,
            !empty($promo->diskon_persen)  => (int) floor($subtotal * ((int) $promo->diskon_persen / 100)),
            default                        => 0,
        };

        return max(0, min($discount, $subtotal));
    }

    // ═══════════════════════════════════════════════════════
    // PROMO
    // ═══════════════════════════════════════════════════════

    private static function applyPromo(Set $set, Get $get): void
    {
        $code = trim((string) $get('promo_code'));

        if (!$code) {
            $set('promotion_id', null);
            self::recalcTotals($set, $get);
            self::notifyWarning('Masukkan kode promo terlebih dahulu');
            return;
        }

        $promo = Promotion::where('kode', $code)
            ->where('is_active', true)
            ->first();

        if (!$promo) {
            $set('promotion_id', null);
            self::recalcTotals($set, $get);
            self::notifyDanger('Kode promo tidak ditemukan atau tidak aktif');
            return;
        }

        $set('promotion_id', $promo->promotion_id);
        self::recalcTotals($set, $get);

        $discountLabel = $promo->diskon_persen
            ? $promo->diskon_persen . '%'
            : 'Rp ' . number_format((int) $promo->diskon_nominal, 0, ',', '.');

        self::notifySuccess('Promo berhasil diterapkan', "Diskon: {$discountLabel}");
    }

    // ═══════════════════════════════════════════════════════
    // RENDERERS (HTML)
    // ═══════════════════════════════════════════════════════

    private static function renderSummaryHtml(Get $get): HtmlString
    {
        $customerName     = self::resolveCustomerName($get);
        $items            = $get('items') ?? [];
        $subtotal         = (int) ($get('subtotal') ?? 0);
        $diskonPromo      = (int) ($get('diskon_total') ?? 0);
        $totalDiskonItem  = self::sumItemDiscounts($items);
        $grand            = max(0, $subtotal - $diskonPromo);

        $paymentMethod    = $get('payment_method');
        $cashReceived     = (int) ($get('cash_received') ?? 0);

        $customerBlock = self::renderCustomerBlock($customerName);
        $itemsBlock    = self::renderItemsBlock($items);
        $totalsBlock   = self::renderTotalsBlock($subtotal, $totalDiskonItem, $diskonPromo, $grand);
        $cashBlock     = self::renderCashBlock($paymentMethod, $cashReceived, $grand);

        return new HtmlString('
            <div style="display:flex; flex-direction:column; gap:14px; padding-top:16px; margin-top:4px; border-top:1px solid #EDE8E3;">
                ' . $customerBlock . $itemsBlock . $totalsBlock . $cashBlock . '
            </div>
        ');
    }

    private static function sumItemDiscounts(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $diskon = max(0, (int) ($item['diskon'] ?? 0));
            // Total diskon item = diskon per-item × qty? Spec: diskon is absolute Rp per line.
            $total += $diskon;
        }
        return $total;
    }

    private static function renderCustomerBlock(?string $name): string
    {
        if (!$name) return '';

        return '
            <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#FAF8F5; border-radius:10px; border:1px solid #EDE8E3;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="#B8956F" style="flex-shrink:0;">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <div style="display:flex; flex-direction:column; min-width:0;">
                    <span style="text-transform:uppercase; font-size:0.6875rem; color:#8A8078; letter-spacing:0.08em; font-weight:600;">Pelanggan</span>
                    <span style="color:#111827; font-weight:600; font-size:0.9375rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . e($name) . '</span>
                </div>
            </div>
        ';
    }

    private static function renderItemsBlock(array $items): string
    {
        $rows = '';

        foreach ($items as $item) {
            $desc       = $item['description'] ?? null;
            $qty        = (int) ($item['qty'] ?? 1);
            $harga      = (int) ($item['harga_satuan'] ?? 0);
            $diskon     = (int) ($item['diskon'] ?? 0);
            $subtotal   = (int) ($item['subtotal'] ?? 0);

            if ($qty < 1 || empty($desc)) continue;

            $rows .= self::renderSingleItemRow($desc, $qty, $harga, $diskon, $subtotal);
        }

        if (!$rows) {
            return '
                <div>
                    <div style="text-transform:uppercase; font-size:0.6875rem; color:#8A8078; letter-spacing:0.1em; font-weight:600; margin-bottom:6px;">Item Pesanan</div>
                    <div style="padding:14px; text-align:center; color:#8A8078; font-size:0.8125rem; background:#FAF8F5; border-radius:10px; border:1px dashed #EDE8E3;">
                        Belum ada item di keranjang
                    </div>
                </div>
            ';
        }

        return '
            <div>
                <div style="text-transform:uppercase; font-size:0.6875rem; color:#8A8078; letter-spacing:0.1em; font-weight:600; margin-bottom:2px;">Item Pesanan</div>
                ' . $rows . '
            </div>
        ';
    }

    /**
     * Render a single cart line with optional item discount:
     * - If diskon > 0: show struck-through original total (qty × harga) and discounted subtotal below.
     * - Below row: small "Diskon: -Rp X" note.
     */
    private static function renderSingleItemRow(string $desc, int $qty, int $harga, int $diskon, int $subtotal): string
    {
        $fmt          = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.');
        $originalLine = $qty * $harga;
        $hasDiskon    = $diskon > 0 && $originalLine > $subtotal;

        if ($hasDiskon) {
            $priceBlock = '
                <div style="display:flex; flex-direction:column; align-items:flex-end;">
                    <span style="color:#8A8078; font-size:0.75rem; text-decoration:line-through;">' . $fmt($originalLine) . '</span>
                    <span style="font-weight:600; color:#111827;">' . $fmt($subtotal) . '</span>
                </div>
            ';
            $diskonNote = '
                <div style="font-size:0.6875rem; color:#DC2626; margin-top:2px;">
                    Diskon: - ' . $fmt($diskon) . '
                </div>
            ';
        } else {
            $priceBlock = '<div style="font-weight:600; color:#111827; white-space:nowrap;">' . $fmt($subtotal) . '</div>';
            $diskonNote = '';
        }

        return '
            <div style="display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px dashed #EDE8E3; font-size:0.875rem;">
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:500; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . e($desc) . '</div>
                    <div style="color:#8A8078; font-size:0.75rem; margin-top:2px;">Qty: ' . $qty . '</div>
                    ' . $diskonNote . '
                </div>
                ' . $priceBlock . '
            </div>
        ';
    }

    private static function renderTotalsBlock(int $subtotal, int $totalDiskonItem, int $diskonPromo, int $grand): string
    {
        $fmt = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.');

        $diskonItemRow = $totalDiskonItem > 0
            ? '
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#111827; font-size:0.875rem;">Total Diskon Item</span>
                    <span style="color:#DC2626; font-weight:600; font-size:0.9375rem;">- ' . $fmt($totalDiskonItem) . '</span>
                </div>
            '
            : '';

        return '
            <div style="display:flex; flex-direction:column; gap:12px; padding-top:14px; border-top:1px solid #EDE8E3; margin-top:4px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#111827; font-size:0.875rem;">Subtotal</span>
                    <span style="color:#111827; font-weight:600; font-size:0.9375rem;">' . $fmt($subtotal) . '</span>
                </div>
                ' . $diskonItemRow . '
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#111827; font-size:0.875rem;">Diskon Promo</span>
                    <span style="color:#DC2626; font-weight:600; font-size:0.9375rem;">- ' . $fmt($diskonPromo) . '</span>
                </div>
                <div style="padding-top:14px; border-top:1px solid #EDE8E3; margin-top:2px;">
                    <div style="text-transform:uppercase; font-size:0.6875rem; color:#8A8078; letter-spacing:0.1em; font-weight:600;">Total Bayar</div>
                    <div style="font-weight:700; font-size:1.875rem; color:#111827; margin-top:6px; letter-spacing:-0.01em;">' . $fmt($grand) . '</div>
                </div>
            </div>
        ';
    }

    /**
     * Cash payment detail: shows customer's cash input vs. total, and change due.
     */
    private static function renderCashBlock(?string $paymentMethod, int $cashReceived, int $grand): string
    {
        if ($paymentMethod !== 'cash') {
            return '';
        }

        $fmt       = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.');
        $kembalian = $cashReceived - $grand;
        $isKurang  = $kembalian < 0;

        $color     = $isKurang ? '#DC2626' : '#065F46';
        $label     = $isKurang ? 'Kurang Bayar' : 'Kembalian';
        $value     = $fmt(abs($kembalian));

        return '
            <div style="padding:14px; background:#FAF8F5; border-radius:10px; border:1px solid #EDE8E3; display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#111827; font-size:0.875rem;">Tunai Diterima</span>
                    <span style="color:#111827; font-weight:600; font-size:0.9375rem;">' . $fmt($cashReceived) . '</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px dashed #EDE8E3;">
                    <span style="color:#111827; font-size:0.875rem; font-weight:500;">' . $label . '</span>
                    <span style="color:' . $color . '; font-weight:700; font-size:1.125rem;">' . $value . '</span>
                </div>
            </div>
        ';
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    private static function resolveCustomerName(Get $get): ?string
    {
        $userId = $get('user_id');
        if (!$userId) return null;

        return User::query()->whereKey($userId)->value('name');
    }

    private static function generateKode(): string
    {
        $today = now()->format('Ymd');
        $prefix = "ORD-{$today}-";

        $last = Order::where('kode_order', 'like', "{$prefix}%")
            ->orderBy('kode_order', 'desc')
            ->value('kode_order');

        $seq = $last ? (int) substr($last, -3) + 1 : 1;

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    private static function notifySuccess(string $title, ?string $body = null): void
    {
        $n = Notification::make()->title($title)->success();
        if ($body) $n->body($body);
        $n->send();
    }

    private static function notifyWarning(string $title): void
    {
        Notification::make()->title($title)->warning()->send();
    }

    private static function notifyDanger(string $title): void
    {
        Notification::make()->title($title)->danger()->send();
    }
}
