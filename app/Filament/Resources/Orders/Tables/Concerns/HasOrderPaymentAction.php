<?php

namespace App\Filament\Resources\Orders\Tables\Concerns;

use App\Models\Order;
use App\Services\Members\ConsultationMembershipService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

trait HasOrderPaymentAction
{
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

                app(ConsultationMembershipService::class)
                    ->activateIfEligibleForOrder($record->fresh(['items.consultation', 'items.appointment.items.consultation']));

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
                            ->with(['product', 'treatment', 'consultation'])
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
                                ?? $consultationName
                                ?? $item->description
                                ?? 'Item';

                            $tipeMap = [
                                'product'      => 'Produk',
                                'treatment'    => 'Treatment',
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
}
