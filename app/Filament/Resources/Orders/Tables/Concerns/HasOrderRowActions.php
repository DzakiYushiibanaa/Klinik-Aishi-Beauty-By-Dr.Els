<?php

namespace App\Filament\Resources\Orders\Tables\Concerns;

use App\Models\Order;
use App\Notifications\OrderShippingUpdated;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;

trait HasOrderRowActions
{
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
                $order = Order::with(['items.product', 'items.treatment', 'pasien.pasienProfile'])
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
                ! in_array($record->shipping_status, ['dikirim', 'selesai', 'dibatalkan'], true) &&
                empty($record->shipping_waybill) &&
                self::isProductOnlyOrder($record)
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
                    $fresh = Order::with(['user.pasienProfile', 'items.product', 'items.treatment'])
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
            ->modalSubmitActionLabel('Batalkan')
            ->modalCancelActionLabel('Kembali')
            ->modalWidth('md')
            ->modalFooterActionsAlignment(Alignment::End)
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
            ->modalSubmitActionLabel('Setujui')
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
            ->modalSubmitActionLabel('Tolak')
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
}
