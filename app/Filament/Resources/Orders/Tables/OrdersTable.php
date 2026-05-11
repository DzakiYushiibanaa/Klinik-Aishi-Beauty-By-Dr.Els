<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Notifications\OrderShippingUpdated;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
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
                $pay = $record->payment_method
                    ? strtoupper($record->payment_method)
                    : '-';
                return '
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight:700; color:#111827;">'.e($total).'</span>
                        <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($pay).'</span>
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
                    'pending'             => 'Pending',
                    'lunas'               => 'Lunas / Sudah Dibayar',
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
     * 💳 Konfirmasi Pembayaran — hanya untuk order yang masih pending/menunggu.
     */
    private static function actionConfirmPayment(): Action
    {
        return Action::make('confirm_payment')
            ->label('')
            ->icon('heroicon-o-wallet')
            ->color('warning')
            ->tooltip('Konfirmasi Pembayaran')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembayaran')
            ->modalDescription(fn (Order $record) =>
                "Pastikan uang sudah masuk untuk invoice {$record->kode_order} sebesar Rp " .
                number_format((int) $record->grand_total, 0, ',', '.') . ". Lanjutkan?"
            )
            ->modalIcon('heroicon-o-wallet')
            ->modalSubmitActionLabel('Ya, Konfirmasi')
            ->modalCancelActionLabel('Batal')
            ->visible(fn (Order $record) => in_array($record->status, ['menunggu_pembayaran', 'pending']))
            ->action(function (Order $record) {
                $record->update([
                    'status'  => 'lunas',
                    'paid_at' => now(),
                ]);

                // Deduct stock (idempotent — service checks the flag)
                app(\App\Services\Orders\ProductStockService::class)->deductForOrder($record->fresh());

                // Notify customer (DB + email)
                self::notifyCustomer($record, 'Pembayaran Dikonfirmasi');

                Notification::make()
                    ->title('Pembayaran berhasil dikonfirmasi')
                    ->success()
                    ->send();
            });
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
                in_array($record->status, ['lunas', 'sudah_dibayar']) &&
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
                "Pesanan {$record->kode_order} akan dibatalkan. Stok akan dikembalikan. Aksi ini tidak bisa dibatalkan."
            )
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->modalCancelActionLabel('Tidak')
            ->schema([
                Textarea::make('cancellation_reason')
                    ->label('Alasan Pembatalan (opsional)')
                    ->placeholder('Tulis alasan pembatalan...')
                    ->rows(3),
            ])
            ->visible(fn (Order $record) => !in_array($record->status, ['batal', 'cancelled']))
            ->action(function (array $data, Order $record): void {
                $reason = $data['cancellation_reason'] ?? null;

                // Restore stock first
                app(\App\Services\Orders\ProductStockService::class)->restoreForOrder($record);

                $record->update([
                    'status'          => 'batal',
                    'shipping_status' => 'dibatalkan',
                ]);

                self::notifyCustomer($record->fresh(), 'Pesanan Dibatalkan', $reason);

                Notification::make()
                    ->title('Pesanan dibatalkan')
                    ->body('Stok telah dikembalikan & pelanggan dinotifikasi.')
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
            'lunas'                => ['Lunas',   '#D1FAE5', '#065F46'],
            'sudah_dibayar'        => ['Lunas',   '#D1FAE5', '#065F46'],
            'paid'                 => ['Lunas',   '#D1FAE5', '#065F46'],
            'menunggu_pembayaran'  => ['Pending', '#FEF3C7', '#92400E'],
            'pending'              => ['Pending', '#FEF3C7', '#92400E'],
            'draft'                => ['Draft',   '#E5E7EB', '#4B5563'],
            'batal'                => ['Batal',   '#FEE2E2', '#991B1B'],
            'cancelled'            => ['Batal',   '#FEE2E2', '#991B1B'],
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
