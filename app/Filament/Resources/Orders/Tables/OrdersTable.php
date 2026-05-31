<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Notifications\OrderShippingUpdated;
use App\Services\FonnteService;
use App\Support\PaymentMethodFormatter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_id', 'desc')
            ->columns([
                self::columnPesananWaktu(),
                self::columnPelanggan(),
                self::columnDetailPengiriman(),
                self::columnAlamat(),
                self::columnTagihan(),
                self::columnStatus(),
            ])
            ->filters(self::filters())
            ->recordActions([
                self::actionConfirmPayment(),   // wallet — hanya untuk pending
                self::actionUpdateResi(),       // truk — input nomor resi
                self::actionView(),              // eye — slide-over detail invoice
                self::actionPrintInvoice(),     // printer
                ActionGroup::make([
                    self::actionApproveCancellation(),
                    self::actionRejectCancellation(),
                    self::actionCancelOrder(),
                ])->label('')->icon('heroicon-o-ellipsis-vertical')->color('gray'),
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

    private static function columnPesananWaktu(): TextColumn
    {
        return TextColumn::make('pesanan_waktu')
            ->label('Pesanan & Waktu')
            ->html()
            ->state(function (Order $record) {
                $kode = e($record->kode_order ?? '-');
                $dt = $record->created_at ? Carbon::parse($record->created_at) : null;
                $waktu = $dt ? $dt->format('d M Y, H:i') : '-';
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="color:#B8956F; font-weight:500;">'.$kode.'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($waktu).'</span>
                    </div>
                ';
            })
            ->searchable(query: fn (Builder $q, string $s) => $q->where('kode_order', 'like', "%{$s}%"))
            ->sortable(query: fn (Builder $q, string $d) => $q->orderBy('created_at', $d));
    }

    private static function columnPelanggan(): TextColumn
    {
        return TextColumn::make('pelanggan')
            ->label('Pelanggan')
            ->html()
            ->state(function (Order $record) {
                $nama = e($record->pasien?->name ?? '-');
                $rm = e($record->pasien?->pasienProfile?->no_rekam_medis ?? '-');
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.$nama.'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$rm.'</span>
                    </div>
                ';
            })
            ->searchable(query: function (Builder $query, string $search) {
                return $query->whereHas('pasien', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pasien.pasienProfile', fn ($q) => $q->where('no_rekam_medis', 'like', "%{$search}%"));
            });
    }

    private static function columnDetailPengiriman(): TextColumn
    {
        return TextColumn::make('detail_pengiriman')
            ->label('Detail & Pengiriman')
            ->html()
            ->state(function (Order $record) {
                $items = $record->items;
                $detail = $items->isEmpty() ? '-' : self::firstItemSummary($items);

                $kurir = $record->shipping_method
                    ? ucwords(str_replace('_', ' ', $record->shipping_method))
                    : '-';
                $resi = $record->shipping_waybill ?: '-';

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="color:#111827;">'.e($detail).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($kurir).' · Resi: '.e($resi).'</span>
                    </div>
                ';
            });
    }

    private static function columnAlamat(): TextColumn
    {
        return TextColumn::make('shipping_address')
            ->label('Alamat')
            ->html()
            ->state(fn (Order $record) => $record->shipping_address
                ? '<span style="color:#111827; font-size:0.875rem;">'.e(\Illuminate\Support\Str::limit($record->shipping_address, 60)).'</span>'
                : '<span style="color:#8A8078; font-size:0.8125rem;">—</span>'
            )
            ->tooltip(fn (Order $record) => $record->shipping_address);
    }

    private static function columnTagihan(): TextColumn
    {
        return TextColumn::make('tagihan')
            ->label('Tagihan')
            ->html()
            ->state(function (Order $record) {
                $total = 'Rp ' . number_format((int) ($record->grand_total ?? 0), 0, ',', '.');

                $isUnpaid = in_array(strtolower((string) ($record->status ?? '')), ['menunggu_pembayaran', 'pending', 'draft'], true);
                $emptyLabel = $isUnpaid ? 'Menunggu Pembayaran' : 'Belum Dipilih';

                $pay = \App\Support\PaymentMethodFormatter::format($record, [
                    'empty_label' => $emptyLabel,
                ]);

                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.e($total).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px; font-weight:500;">'.e($pay).'</span>
                    </div>
                ';
            })
            ->sortable(query: fn (Builder $q, string $d) => $q->orderBy('grand_total', $d));
    }

    private static function columnStatus(): TextColumn
    {
        return TextColumn::make('status')
            ->label('Status')
            ->html()
            ->state(function (Order $record) {
                return self::renderStatusBadge($record->status)
                    . self::renderShippingBadge($record->shipping_status);
            });
    }

    // ═══════════════════════════════════════════════════════
    // FILTERS
    // ═══════════════════════════════════════════════════════

    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status Pembayaran')
                ->options([
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'cancel_requested'    => 'Menunggu Konfirmasi Pembatalan',
                    'sudah_dibayar'       => 'Sudah Dibayar',
                    'batal'               => 'Batal',
                    'draft'               => 'Draft',
                ]),

            SelectFilter::make('shipping_status')
                ->label('Status Pengiriman')
                ->options([
                    'belum_dikirim' => 'Belum Dikirim',
                    'diproses'      => 'Sedang Diproses',
                    'dikemas'       => 'Dikemas',
                    'dikirim'       => 'Dikirim',
                    'selesai'       => 'Selesai',
                    'dibatalkan'    => 'Dibatalkan',
                ]),
        ];
    }

    // ═══════════════════════════════════════════════════════
    // ROW ACTIONS
    // ═══════════════════════════════════════════════════════

    /**
     * 👁️ View — slide-over with invoice-style layout (mirrors PDF invoice).
     */
    private static function actionView(): Action
    {
        return Action::make('view_invoice')
            ->label('')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->tooltip('Lihat Detail Invoice')
            ->slideOver()
            ->stickyModalFooter()
            ->modalHeading(fn (Order $record) => 'Invoice ' . $record->kode_order)
            ->modalWidth('2xl')
            ->modalCancelActionLabel('Tutup')
            ->modalSubmitAction(false)
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalContent(function (Order $record) {
                $order = Order::with(['items.product', 'items.treatment', 'items.bundle', 'pasien.pasienProfile'])
                    ->find($record->order_id);

                return view('filament.orders.invoice-slideover', ['order' => $order]);
            })
            ->extraModalFooterActions([
                Action::make('print_invoice')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Order $record) => \Illuminate\Support\Facades\Route::has('admin.orders.invoice')
                        ? route('admin.orders.invoice', $record->order_id)
                        : '#', shouldOpenInNewTab: true)
                    ->visible(fn () => \Illuminate\Support\Facades\Route::has('admin.orders.invoice')),
            ]);
    }

    /**
     * 💳 Konfirmasi Pembayaran — flow dua tahap untuk admin/frontdesk:
     *
     *   1. Klik tombol → modal slide review terbuka:
     *      - Ringkasan order (kode, customer, status, tanggal, total)
     *      - Daftar item dari `order_items`
     *      - Ringkasan tagihan (subtotal, diskon, ongkir, grand total)
     *      - Pilih metode pembayaran
     *      - Nominal dibayar (cash → editable + kembalian, lainnya → readonly)
     *
     *   2. Klik "Konfirmasi Lunas" → konfirmasi terakhir lewat wire:confirm
     *      sebelum benar-benar mengubah status order.
     *
     *   3. Order di-update: status `sudah_dibayar`, paid_at, payment_method,
     *      cash_received (kalau ada). Tidak menambah tabel atau kolom baru.
     */
    private static function actionConfirmPayment(): Action
    {
        return Action::make('confirm_payment')
            ->label('')
            ->icon('heroicon-o-wallet')
            ->color('warning')
            ->tooltip('Konfirmasi Pembayaran')
            ->modalHeading('Konfirmasi Pembayaran')
            ->modalDescription('Periksa detail transaksi dan pilih metode pembayaran sebelum menandai transaksi sebagai lunas.')
            ->modalIcon('heroicon-o-wallet')
            ->modalSubmitActionLabel('Konfirmasi Lunas')
            ->modalCancelActionLabel('Batal')
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalWidth('2xl')
            ->stickyModalFooter()
            ->visible(fn (Order $record) => in_array($record->status, ['menunggu_pembayaran', 'pending']))
            ->fillForm(fn (Order $record) => [
                'payment_method' => $record->payment_method ?: 'cash',
                'diskon_total'   => (int) ($record->diskon_total ?? 0),
                'cash_received'  => (int) ($record->cash_received ?: $record->grand_total),
            ])
            ->schema(fn (Order $record) => [
                self::sectionRingkasanOrder($record),
                self::sectionDetailItem($record),
                self::sectionRingkasanTagihan($record),
                self::sectionMetodePembayaran($record),
            ])
            ->action(function (array $data, Order $record): void {
                // Debug log — pastikan handler benar-benar terpanggil saat
                // tombol "Konfirmasi Lunas" diklik. Cek storage/logs/laravel.log.
                \Illuminate\Support\Facades\Log::info('Confirm payment clicked', [
                    'order_id' => $record->order_id,
                    'kode'     => $record->kode_order,
                    'data'     => $data,
                ]);

                // Validasi metode pembayaran wajib dipilih.
                if (empty($data['payment_method'])) {
                    Notification::make()
                        ->title('Metode pembayaran wajib dipilih')
                        ->danger()
                        ->send();
                    return;
                }

                // Hitung ulang total tagihan dengan diskon transaksi terbaru.
                // Pakai cap yang sama dengan UI (mengikuti rule shipping).
                $cap    = self::calculateBillingCap($record);
                $diskon = max(0, min((int) ($data['diskon_total'] ?? $record->diskon_total ?? 0), $cap));
                $grandTotal = max(0, $cap - $diskon);

                // Validasi cash: nominal dibayar harus >= grand total baru.
                if ($data['payment_method'] === 'cash') {
                    $cashReceived = (int) ($data['cash_received'] ?? 0);
                    if ($cashReceived < $grandTotal) {
                        Notification::make()
                            ->title('Nominal pembayaran kurang')
                            ->body('Nominal yang diterima minimal Rp '.number_format($grandTotal, 0, ',', '.').'.')
                            ->danger()
                            ->send();
                        return;
                    }
                }

                $payload = [
                    'status'         => 'sudah_dibayar',
                    'paid_at'        => now(),
                    'payment_method' => $data['payment_method'],
                    'diskon_total'   => $diskon,
                    'grand_total'    => $grandTotal,
                ];

                // cash_received hanya relevan kalau metode = cash.
                if ($data['payment_method'] === 'cash' && isset($data['cash_received'])) {
                    $payload['cash_received'] = max(0, (int) $data['cash_received']);
                }

                $record->update($payload);

                \Illuminate\Support\Facades\Log::info('Order confirmed paid', [
                    'order_id'    => $record->order_id,
                    'kode'        => $record->kode_order,
                    'grand_total' => $grandTotal,
                    'method'      => $data['payment_method'],
                ]);

                // Deduct stock (idempotent).
                app(\App\Services\Orders\ProductStockService::class)->deductForOrder($record->fresh());

                // Notify customer (DB + email).
                self::notifyCustomer($record, 'Pembayaran Dikonfirmasi');

                Notification::make()
                    ->title('Pembayaran berhasil dikonfirmasi')
                    ->body('Status transaksi ' . $record->kode_order . ' sekarang Lunas.')
                    ->success()
                    ->duration(5000)
                    ->send();
            });
    }

    // ───────────────────────────────────────────────────────
    // SCHEMA SECTIONS untuk modal Konfirmasi Pembayaran
    // ───────────────────────────────────────────────────────

    private static function sectionRingkasanOrder(Order $order): Section
    {
        return Section::make('Ringkasan Order')
            ->compact()
            ->schema([
                Placeholder::make('order_summary')
                    ->hiddenLabel()
                    ->content(function () use ($order): HtmlString {
                        $kode = e($order->kode_order ?? '-');
                        $nama = e($order->user?->name ?? ($order->pasien?->name ?? 'Customer'));
                        $tanggal = $order->created_at
                            ? Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i')
                            : '-';
                        $total = number_format((int) ($order->grand_total ?? 0), 0, ',', '.');

                        $statusMap = [
                            'menunggu_pembayaran' => ['Menunggu Pembayaran', '#FEF3C7', '#92400E'],
                            'pending'             => ['Menunggu Pembayaran', '#FEF3C7', '#92400E'],
                            'sudah_dibayar'       => ['Lunas',                '#D1FAE5', '#065F46'],
                            'batal'               => ['Dibatalkan',           '#FEE2E2', '#991B1B'],
                        ];
                        [$statusLabel, $statusBg, $statusColor] =
                            $statusMap[strtolower((string) $order->status)] ?? ['—', '#E5E7EB', '#4B5563'];

                        return new HtmlString('
                            <div style="padding:16px 18px; background:linear-gradient(135deg,#FAF8F5 0%,#FFFFFF 100%); border:1px solid #EDE8E3; border-radius:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                                    <div style="min-width:0;">
                                        <div style="font-family:ui-monospace,SFMono-Regular,monospace; font-size:0.875rem; color:#8A8078; letter-spacing:0.02em;">'.$kode.'</div>
                                        <div style="font-size:1.05rem; font-weight:700; color:#1A1A1A; margin-top:4px;">'.$nama.'</div>
                                        <div style="font-size:0.85rem; color:#8A8078; margin-top:6px;">'.$tanggal.' WIB</div>
                                    </div>
                                    <div style="text-align:right; min-width:160px;">
                                        <span style="display:inline-block; padding:4px 12px; background:'.$statusBg.'; color:'.$statusColor.'; font-size:0.7rem; font-weight:700; border-radius:9999px; letter-spacing:0.05em; text-transform:uppercase;">
                                            '.e($statusLabel).'
                                        </span>
                                        <div style="font-size:0.75rem; color:#8A8078; margin-top:8px; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Total Tagihan</div>
                                        <div style="font-size:1.25rem; font-weight:700; color:#B8956F; font-variant-numeric:tabular-nums;">Rp '.$total.'</div>
                                    </div>
                                </div>
                            </div>
                        ');
                    }),
            ]);
    }

    private static function sectionDetailItem(Order $order): Section
    {
        return Section::make('Detail Item Transaksi')
            ->compact()
            ->schema([
                Placeholder::make('item_list')
                    ->hiddenLabel()
                    ->content(function () use ($order): HtmlString {
                        $items = $order->items()
                            ->with(['product', 'treatment', 'bundle', 'consultation'])
                            ->get();

                        if ($items->isEmpty()) {
                            return new HtmlString(
                                '<div style="padding:14px; color:#94A3B8; font-style:italic; text-align:center; background:#FAF8F5; border:1px dashed #EDE8E3; border-radius:10px;">Tidak ada item.</div>'
                            );
                        }

                        $rows = '';
                        foreach ($items as $item) {
                            // Resolver nama item dengan fallback aman:
                            //   produk     → product.nama
                            //   treatment  → treatment.nama
                            //   bundle     → bundle.nama (prefixed)
                            //   konsultasi → consultation.nama (data baru) atau description (data lama)
                            //   selainnya  → description
                            $consultationName = $item->consultation?->nama
                                ?? $item->consultation?->name
                                ?? $item->description;

                            $name = $item->product?->nama
                                ?? $item->treatment?->nama
                                ?? ($item->bundle ? 'Bundle: '.$item->bundle->nama : null)
                                ?? $consultationName
                                ?? $item->description
                                ?? 'Item';

                            $tipeMap = [
                                'product'      => 'Produk',
                                'treatment'    => 'Treatment',
                                'bundle'       => 'Bundle',
                                'consultation' => 'Konsultasi',
                            ];
                            $tipeLabel = $tipeMap[strtolower((string) $item->item_type)] ?? ucfirst((string) $item->item_type);

                            $qty       = (int) ($item->qty ?? 1);
                            $unitPrice = (int) ($item->harga_satuan ?? 0);
                            $diskon    = (int) ($item->diskon ?? 0);
                            $subtotal  = (int) ($item->subtotal ?? ($unitPrice * $qty - $diskon));

                            $diskonLine = $diskon > 0
                                ? '<div style="font-size:0.7rem; color:#DC2626; margin-top:2px;">Diskon: -Rp '.number_format($diskon, 0, ',', '.').'</div>'
                                : '';

                            $rows .= '
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #EDE8E3;">
                                    <div style="min-width:0; flex:1;">
                                        <div style="font-size:0.875rem; font-weight:600; color:#1A1A1A;">'.e($name).'</div>
                                        <div style="font-size:0.7rem; color:#8A8078; margin-top:3px;">'
                                            .e($tipeLabel).' • Qty '.$qty
                                            .' • @Rp '.number_format($unitPrice, 0, ',', '.').'
                                        </div>
                                        '.$diskonLine.'
                                    </div>
                                    <div style="font-size:0.875rem; font-weight:700; color:#1A1A1A; font-variant-numeric:tabular-nums; white-space:nowrap;">
                                        Rp '.number_format($subtotal, 0, ',', '.').'
                                    </div>
                                </div>
                            ';
                        }

                        // Hilangkan border-bottom row terakhir.
                        return new HtmlString(
                            '<div style="background:#FFFFFF; border:1px solid #EDE8E3; border-radius:10px; padding:4px 14px;">'
                            .preg_replace('/border-bottom:1px solid #EDE8E3;(?=[^;]*$)/', 'border-bottom:none;', $rows)
                            .'</div>'
                        );
                    }),
            ]);
    }

    private static function sectionRingkasanTagihan(Order $order): Section
    {
        $hasShipping = self::orderHasShipping($order);

        return Section::make('Ringkasan Tagihan')
            ->compact()
            ->schema([
                // Rincian static (subtotal + ongkir kalau ada) — tidak editable.
                Placeholder::make('billing_lines')
                    ->hiddenLabel()
                    ->content(function () use ($order, $hasShipping): HtmlString {
                        $rupiah = fn ($n) => 'Rp '.number_format((int) ($n ?? 0), 0, ',', '.');

                        $subtotal = (int) ($order->subtotal ?? 0);
                        $ongkir   = (int) ($order->ongkir ?? 0);

                        $row = fn (string $label, string $value) =>
                            '<div style="display:flex; justify-content:space-between; padding:6px 0; font-size:0.875rem; color:#475569;">
                                <span>'.e($label).'</span>
                                <span style="font-variant-numeric:tabular-nums;">'.$value.'</span>
                            </div>';

                        $html = '<div style="background:#FAF8F5; border:1px solid #EDE8E3; border-top-left-radius:10px; border-top-right-radius:10px; border-bottom:none; padding:12px 16px 4px;">';
                        $html .= $row('Subtotal', $rupiah($subtotal));
                        if ($hasShipping && $ongkir > 0) {
                            $html .= $row('Ongkos Kirim', $rupiah($ongkir));
                        }
                        $html .= '</div>';

                        return new HtmlString($html);
                    }),

                // Diskon transaksi — editable oleh kasir.
                TextInput::make('diskon_total')
                    ->label('Diskon Transaksi')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->default((int) ($order->diskon_total ?? 0))
                    ->reactive()
                    ->helperText(function (Get $get) use ($order): string {
                        $cap = self::calculateBillingCap($order);
                        $diskon = (int) ($get('diskon_total') ?? 0);

                        if ($diskon > $cap) {
                            return 'Diskon tidak boleh melebihi total tagihan. Akan dipotong otomatis menjadi Rp '
                                .number_format($cap, 0, ',', '.').' saat disimpan.';
                        }
                        return 'Maksimal diskon Rp '.number_format($cap, 0, ',', '.').'.';
                    })
                    ->rules([
                        function () use ($order) {
                            return function (string $attribute, $value, \Closure $fail) use ($order) {
                                $value = (int) $value;
                                $cap = self::calculateBillingCap($order);
                                if ($value < 0) {
                                    $fail('Diskon tidak boleh negatif.');
                                }
                                if ($value > $cap) {
                                    $fail('Diskon tidak boleh melebihi total tagihan (Rp '.number_format($cap, 0, ',', '.').').');
                                }
                            };
                        },
                    ])
                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($order) {
                        // Saat diskon berubah, kalau metode bukan cash maka
                        // cash_received auto-mengikuti grand total baru.
                        if ($get('payment_method') !== 'cash') {
                            $set('cash_received', self::calculateGrandTotal($order, $get));
                        }
                    }),

                // Preview Total Pembayaran (real-time mengikuti diskon).
                Placeholder::make('total_preview')
                    ->hiddenLabel()
                    ->content(function (Get $get) use ($order): HtmlString {
                        $rupiah = fn ($n) => 'Rp '.number_format((int) ($n ?? 0), 0, ',', '.');

                        $cap    = self::calculateBillingCap($order);
                        $diskon = max(0, min((int) ($get('diskon_total') ?? 0), $cap));
                        $grand  = max(0, $cap - $diskon);

                        $diskonRow = $diskon > 0
                            ? '<div style="display:flex; justify-content:space-between; padding:4px 0; font-size:0.875rem; color:#DC2626;">
                                    <span>Diskon Transaksi</span>
                                    <span style="font-variant-numeric:tabular-nums;">-'.$rupiah($diskon).'</span>
                                </div>'
                            : '';

                        return new HtmlString('
                            <div style="background:#FAF8F5; border:1px solid #EDE8E3; border-top:none; border-bottom-left-radius:10px; border-bottom-right-radius:10px; padding:6px 16px 12px; margin-top:-1px;">
                                '.$diskonRow.'
                                <div style="border-top:1px dashed #E8E0D8; margin-top:6px; padding-top:8px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:0.8rem; color:#8A8078; text-transform:uppercase; letter-spacing:0.06em; font-weight:600;">Total Pembayaran</span>
                                        <span style="font-size:1.25rem; font-weight:700; color:#B8956F; font-variant-numeric:tabular-nums;">'.$rupiah($grand).'</span>
                                    </div>
                                </div>
                            </div>
                        ');
                    }),
            ]);
    }

    private static function sectionMetodePembayaran(Order $order): Section
    {
        return Section::make('Metode Pembayaran')
            ->compact()
            ->schema([
                Select::make('payment_method')
                    ->label('Pilih Metode')
                    ->options([
                        'qris'          => 'QRIS',
                        'bca'           => 'BCA Transfer',
                        'bsi'           => 'BSI Transfer',
                        'cash'          => 'Tunai / Cash',
                    ])
                    ->native(false)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($order) {
                        // Saat ganti metode bukan-cash, reset cash_received ke total
                        // pembayaran terbaru (subtotal + ongkir - diskon transaksi).
                        if ($state !== 'cash') {
                            $set('cash_received', self::calculateGrandTotal($order, $get));
                        }
                    }),

                Grid::make(['default' => 1, 'md' => 2])->schema([
                    TextInput::make('cash_received')
                        ->label('Nominal Dibayar')
                        ->prefix('Rp')
                        ->numeric()
                        ->minValue(0)
                        ->default(fn (Get $get) => self::calculateGrandTotal($order, $get))
                        ->required()
                        ->reactive()
                        ->disabled(fn (Get $get) => $get('payment_method') !== 'cash')
                        ->dehydrated()
                        ->helperText(fn (Get $get) =>
                            $get('payment_method') === 'cash'
                                ? 'Masukkan nominal uang yang diterima dari pasien.'
                                : 'Otomatis mengikuti total tagihan.'
                        ),

                    Placeholder::make('change_preview')
                        ->label('Kembalian')
                        ->visible(fn (Get $get) => $get('payment_method') === 'cash')
                        ->content(function (Get $get) use ($order): HtmlString {
                            $received = (int) ($get('cash_received') ?? 0);
                            $total    = self::calculateGrandTotal($order, $get);
                            $change   = $received - $total;

                            // Kurang bayar — highlight merah, label "Kurang".
                            if ($change < 0) {
                                return new HtmlString('
                                    <div style="display:flex; flex-direction:column; gap:2px;">
                                        <span style="font-size:0.7rem; font-weight:600; color:#DC2626; text-transform:uppercase; letter-spacing:0.06em;">Kurang</span>
                                        <span style="font-size:1.05rem; font-weight:700; color:#DC2626; font-variant-numeric:tabular-nums;">Rp '
                                            .number_format(abs($change), 0, ',', '.')
                                        .'</span>
                                    </div>
                                ');
                            }

                            // Pas atau lebih — highlight gold/hijau.
                            $color = $change > 0 ? '#065F46' : '#1A1A1A';
                            return new HtmlString('
                                <div style="display:flex; flex-direction:column; gap:2px;">
                                    <span style="font-size:0.7rem; font-weight:600; color:#8A8078; text-transform:uppercase; letter-spacing:0.06em;">Kembalian</span>
                                    <span style="font-size:1.05rem; font-weight:700; color:'.$color.'; font-variant-numeric:tabular-nums;">Rp '
                                        .number_format($change, 0, ',', '.')
                                    .'</span>
                                </div>
                            ');
                        }),
                ]),
            ]);
    }

    /**
     * Hitung grand total dari state form (subtotal + ongkir? - diskon)
     * dengan validasi minimum 0. Dipakai oleh Metode Pembayaran &
     * preview Kembalian agar konsisten dengan input diskon di section
     * Ringkasan Tagihan.
     */
    private static function calculateGrandTotal(Order $order, Get $get): int
    {
        $cap    = self::calculateBillingCap($order);
        $diskon = max(0, min((int) ($get('diskon_total') ?? $order->diskon_total ?? 0), $cap));

        return max(0, $cap - $diskon);
    }

    /**
     * Cap total tagihan = subtotal + ongkir (kalau ada pengiriman).
     * Untuk transaksi layanan klinik (konsultasi/treatment), ongkir
     * di-skip dari kalkulasi sehingga diskon dibatasi murni oleh subtotal.
     */
    private static function calculateBillingCap(Order $order): int
    {
        $subtotal = (int) ($order->subtotal ?? 0);
        $ongkir   = self::orderHasShipping($order) ? (int) ($order->ongkir ?? 0) : 0;
        return $subtotal + $ongkir;
    }

    /**
     * Order dianggap punya pengiriman kalau:
     *   - shipping_method ter-set (pakai kurir), ATAU
     *   - shipping_address ter-isi, ATAU
     *   - ongkir > 0 (fallback paling simple).
     *
     * Konsultasi & treatment di klinik tidak punya ketiganya, jadi
     * row ongkir & wording shipping otomatis disembunyikan.
     */
    private static function orderHasShipping(Order $order): bool
    {
        if (! empty($order->shipping_method)) return true;
        if (! empty($order->shipping_address)) return true;
        if ((int) ($order->ongkir ?? 0) > 0) return true;

        return false;
    }

    /**
     * 🚚 Update Resi Pengiriman — set waybill + change shipping_status to 'dikirim'.
     */
    private static function actionUpdateResi(): Action
    {
        return Action::make('update_resi')
            ->label('')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->tooltip('Input / Update Nomor Resi')
            ->modalHeading('Update Resi Pengiriman')
            ->modalWidth('md')
            ->modalSubmitActionLabel('Simpan Resi')
            ->modalCancelActionLabel('Batal')
            ->modalFooterActionsAlignment(Alignment::End)
            ->visible(fn (Order $record) =>
                $record->status === 'sudah_dibayar' &&
                $record->shipping_status !== 'selesai'
            )
            ->fillForm(fn (Order $record) => [
                'shipping_waybill' => $record->shipping_waybill,
            ])
            ->schema(fn (Order $record) => [
                Placeholder::make('kurir')
                    ->hiddenLabel()
                    ->content(function () use ($record): HtmlString {
                        $kurir = $record->shipping_method
                            ? ucwords(str_replace('_', ' ', $record->shipping_method))
                            : 'Belum dipilih';
                        return new HtmlString('
                            <div style="padding:12px 14px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px; color:#7A5A3A; font-size:0.875rem;">
                                <span style="font-weight:500;">Kurir:</span> '.e($kurir).'
                            </div>
                        ');
                    }),

                TextInput::make('shipping_waybill')
                    ->label('Masukkan Nomor Resi')
                    ->placeholder('Contoh: JP123456')
                    ->maxLength(100)
                    ->required(),
            ])
            ->action(function (array $data, Order $record): void {
                $record->update([
                    'shipping_waybill' => $data['shipping_waybill'],
                    'shipping_status'  => 'dikirim',
                ]);

                // Notify customer about shipping (uses existing OrderShippingUpdated)
                if ($record->pasien) {
                    $record->pasien->notify(new OrderShippingUpdated($record->fresh()));
                }

                // 🔔 WhatsApp ke pasien (best-effort).
                try {
                    $fresh = Order::with(['user.pasienProfile', 'items.product', 'items.treatment', 'items.bundle'])
                        ->find($record->order_id);
                    if ($fresh) {
                        app(FonnteService::class)->sendOrderShipped($fresh);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (order shipped): '.$e->getMessage());
                }

                Notification::make()
                    ->title('Resi berhasil disimpan')
                    ->body('Pesanan ditandai sebagai dikirim & notifikasi telah terkirim.')
                    ->success()
                    ->send();
            });
    }

    /**
     * 🖨️ Cetak Invoice — open struct-thermal print page in new tab.
     */
    private static function actionPrintInvoice(): Action
    {
        return Action::make('print')
            ->label('')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->tooltip('Cetak Invoice')
            ->url(fn (Order $record) => \Illuminate\Support\Facades\Route::has('admin.orders.invoice')
                ? route('admin.orders.invoice', $record->order_id)
                : '#', shouldOpenInNewTab: true)
            ->visible(fn () => \Illuminate\Support\Facades\Route::has('admin.orders.invoice'));
    }

    /**
     * ❌ Batalkan Pesanan — inside 3-dots menu.
     */
    private static function actionCancelOrder(): Action
    {
        return Action::make('cancel_order')
            ->label('Batalkan Pesanan')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Pesanan?')
            ->modalDescription(fn (Order $record) =>
                "Pesanan {$record->kode_order} akan dibatalkan dan stok dikembalikan otomatis."
            )
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->modalCancelActionLabel('Kembali')
            ->modalWidth('md')
            ->schema([
                Textarea::make('cancellation_reason')
                    ->label('Alasan Pembatalan (opsional)')
                    ->placeholder('Tulis alasan pembatalan...')
                    ->rows(3),
            ])
            ->visible(fn (Order $record) => !in_array($record->status, ['batal', 'cancelled', 'dibatalkan', 'cancel_requested', 'sudah_dibayar']))
            ->action(function (array $data, Order $record): void {
                $reason = $data['cancellation_reason'] ?? null;
                $cancelledOrder = null;

                \Illuminate\Support\Facades\DB::transaction(function () use ($record, &$cancelledOrder) {
                    $lockedOrder = Order::with('items')->lockForUpdate()->find($record->getKey());

                    if (!$lockedOrder || in_array($lockedOrder->status, ['batal', 'cancelled', 'dibatalkan'], true)) {
                        return;
                    }

                    self::markLegacyOnlineStockDeductedIfNeeded($lockedOrder);

                    app(\App\Services\Orders\ProductStockService::class)->restoreForOrder($lockedOrder);

                    $lockedOrder->update([
                        'status'          => 'batal',
                        'shipping_status' => 'dibatalkan',
                    ]);

                    $cancelledOrder = $lockedOrder->fresh();
                });

                if (!$cancelledOrder) {
                    Notification::make()
                        ->title('Pesanan sudah diproses')
                        ->body('Status pesanan sudah berubah, stok tidak diproses ulang.')
                        ->warning()
                        ->send();
                    return;
                }

                self::notifyCustomer($cancelledOrder, 'Pesanan Dibatalkan', $reason);

                Notification::make()
                    ->title('Pesanan dibatalkan')
                    ->body('Stok telah dikembalikan & pelanggan dinotifikasi.')
                    ->success()
                    ->send();
            });
    }

    /**
     * ✅ Setujui Pembatalan — admin/apoteker mengonfirmasi pengajuan
     * pembatalan dari pasien. Stok dikembalikan dan order ditandai batal.
     * Hanya tampil untuk order dengan status `cancel_requested`.
     */
    private static function actionApproveCancellation(): Action
    {
        return Action::make('approve_cancellation')
            ->label('Setujui Pembatalan')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->modalHeading('Setujui Pembatalan Pesanan?')
            ->modalDescription('')
            ->modalSubmitActionLabel('Ya, Setujui')
            ->modalCancelActionLabel('Kembali')
            ->modalSubmitAction(fn ($action) => $action->color('primary'))
            ->modalWidth('md')
            ->modalAlignment(Alignment::Center)
            ->modalFooterActionsAlignment(Alignment::End)
            ->visible(fn (Order $record) => $record->status === 'cancel_requested')
            ->schema([
                Placeholder::make('confirm_body')
                    ->hiddenLabel()
                    ->content(fn (Order $record) => new HtmlString('
                        <div style="text-align:center; padding:0.25rem 0 0.5rem;">
                            <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:9999px; background:rgba(184,149,111,0.12); margin-bottom:0.875rem;">
                                <svg style="width:30px; height:30px; color:#B8956F;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p style="font-size:0.95rem; color:#475569; line-height:1.6; margin:0 0 0.5rem;">
                                Apakah Anda yakin ingin menyetujui pembatalan pesanan ini?
                            </p>
                            <p style="font-size:0.75rem; color:#8C7A63; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; letter-spacing:0.02em; margin:0;">
                                '.e($record->kode_order).'
                            </p>
                        </div>
                    ')),
            ])
            ->action(function (Order $record): void {
                $cancelledOrder = null;

                \Illuminate\Support\Facades\DB::transaction(function () use ($record, &$cancelledOrder) {
                    $lockedOrder = Order::with('items')->lockForUpdate()->find($record->getKey());

                    if (!$lockedOrder || $lockedOrder->status !== 'cancel_requested') {
                        return;
                    }

                    self::markLegacyOnlineStockDeductedIfNeeded($lockedOrder);

                    app(\App\Services\Orders\ProductStockService::class)->restoreForOrder($lockedOrder);

                    $lockedOrder->update([
                        'status'          => 'batal',
                        'shipping_status' => 'dibatalkan',
                    ]);

                    $cancelledOrder = $lockedOrder->fresh();
                });

                if (!$cancelledOrder) {
                    Notification::make()
                        ->title('Pembatalan sudah diproses')
                        ->body('Status pesanan sudah berubah, stok tidak diproses ulang.')
                        ->warning()
                        ->send();
                    return;
                }

                self::notifyCustomer($cancelledOrder, 'Pengajuan Pembatalan Disetujui');

                Notification::make()
                    ->title('Pesanan berhasil dibatalkan')
                    ->body("Pengajuan pembatalan {$cancelledOrder->kode_order} disetujui & stok dikembalikan.")
                    ->success()
                    ->send();
            });
    }

    /**
     * ✋ Tolak Pembatalan — admin/apoteker menolak pengajuan pembatalan
     * dari pasien. Order kembali ke status menunggu_pembayaran.
     */
    private static function actionRejectCancellation(): Action
    {
        return Action::make('reject_cancellation')
            ->label('Tolak Pembatalan')
            ->icon('heroicon-o-x-mark')
            ->color('warning')
            ->modalHeading('Tolak Pembatalan Pesanan?')
            ->modalDescription('')
            ->modalSubmitActionLabel('Ya, Tolak')
            ->modalCancelActionLabel('Kembali')
            ->modalWidth('md')
            ->modalAlignment(Alignment::Center)
            ->modalFooterActionsAlignment(Alignment::End)
            ->visible(fn (Order $record) => $record->status === 'cancel_requested')
            ->schema([
                Placeholder::make('reject_body')
                    ->hiddenLabel()
                    ->content(fn (Order $record) => new HtmlString('
                        <div style="text-align:center; padding:0.25rem 0 0.5rem;">
                            <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:9999px; background:rgba(245,158,11,0.14); margin-bottom:0.875rem;">
                                <svg style="width:30px; height:30px; color:#D97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <p style="font-size:0.95rem; color:#475569; line-height:1.6; margin:0 0 0.5rem;">
                                Apakah Anda yakin ingin menolak pembatalan pesanan ini?
                            </p>
                            <p style="font-size:0.75rem; color:#8C7A63; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; letter-spacing:0.02em; margin:0;">
                                '.e($record->kode_order).'
                            </p>
                        </div>
                    ')),
            ])
            ->action(function (Order $record): void {
                $record->update([
                    'status' => 'menunggu_pembayaran',
                ]);

                self::notifyCustomer($record->fresh(), 'Pengajuan Pembatalan Ditolak');

                Notification::make()
                    ->title('Pengajuan pembatalan ditolak')
                    ->body("Pesanan {$record->kode_order} dikembalikan ke status menunggu pembayaran.")
                    ->success()
                    ->send();
            });
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Send in-app DB notification + email to the order's customer.
     * Uses existing OrderShippingUpdated for status/shipping changes.
     */
    private static function notifyCustomer(Order $order, string $title, ?string $extra = null): void
    {
        if (!$order->pasien) return;

        $order->pasien->notify(new OrderShippingUpdated($order));
    }

    private static function markLegacyOnlineStockDeductedIfNeeded(Order $order): void
    {
        if ($order->is_stock_deducted || $order->sumber !== 'online') {
            return;
        }

        $order->loadMissing('items');

        $hasProductItem = $order->items->contains(fn ($item) => !empty($item->product_id));

        if (!$hasProductItem) {
            return;
        }

        $order->forceFill(['is_stock_deducted' => true])->save();
    }

    private static function firstItemSummary($items): string
    {
        $first = $items->first();
        $qty = (int) ($first->qty ?? 1);

        $name = match (true) {
            $first->item_type === 'product' && $first->product     => $first->product->nama,
            $first->item_type === 'treatment' && $first->treatment => $first->treatment->nama,
            $first->item_type === 'bundle' && $first->bundle       => 'Bundle: ' . $first->bundle->nama,
            default                                                 => ($first->description ?: ucfirst((string) $first->item_type)),
        };

        if ($qty > 1) $name .= " x{$qty}";

        $rest = $items->count() - 1;
        if ($rest > 0) $name .= " + {$rest} item";

        return $name;
    }

    private static function renderStatusBadge(?string $status): string
    {
        $map = [
            'sudah_dibayar'        => ['Sudah Dibayar', '#D1FAE5', '#065F46'],
            'menunggu_pembayaran'  => ['Pending', '#FEF3C7', '#92400E'],
            'pending'              => ['Pending', '#FEF3C7', '#92400E'],
            'cancel_requested'     => ['Menunggu Konfirmasi Pembatalan', '#FFEDD5', '#9A3412'],
            'draft'                => ['Draft',   '#E5E7EB', '#4B5563'],
            'batal'                => ['Batal',   '#FEE2E2', '#991B1B'],
            'cancelled'            => ['Batal',   '#FEE2E2', '#991B1B'],
            'dibatalkan'           => ['Batal',   '#FEE2E2', '#991B1B'],
        ];

        $cfg = $map[strtolower((string) $status)] ?? [ucfirst((string) $status ?: '-'), '#E5E7EB', '#4B5563'];
        [$label, $bg, $color] = $cfg;

        return '<div style="margin-bottom:4px;">
            <span style="display:inline-block; padding:4px 14px; border-radius:9999px; background:'.$bg.'; color:'.$color.'; font-size:0.75rem; font-weight:600;">'.e($label).'</span>
        </div>';
    }

    private static function renderShippingBadge(?string $shippingStatus): string
    {
        if (!$shippingStatus || $shippingStatus === 'belum_dikirim') {
            return '';
        }

        $map = [
            'diproses'   => ['Diproses',  '#FFFFFF', '#4B5563', '#D1D5DB'],
            'dikemas'    => ['Dikemas',   '#FFFFFF', '#4B5563', '#D1D5DB'],
            'dikirim'    => ['Dikirim',   '#EFF6FF', '#1E40AF', '#BFDBFE'],
            'selesai'    => ['Selesai',   '#ECFDF5', '#065F46', '#A7F3D0'],
            'dibatalkan' => ['Dibatalkan','#FEF2F2', '#991B1B', '#FECACA'],
        ];

        $cfg = $map[$shippingStatus] ?? null;
        if (!$cfg) return '';
        [$label, $bg, $color, $border] = $cfg;

        return '<span style="display:inline-block; padding:3px 10px; border-radius:9999px; background:'.$bg.'; color:'.$color.'; border:1px solid '.$border.'; font-size:0.6875rem; font-weight:500;">'.e($label).'</span>';
    }
}
