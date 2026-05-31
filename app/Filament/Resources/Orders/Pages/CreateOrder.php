<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\PosCreateOrderForm;
use App\Models\Order;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Transaksi Baru';
    }

    public function getBreadcrumb(): string
    {
        return 'Kembali ke Transaksi';
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        // Hide default footer submit — we use in-sidebar button instead
        return parent::getCreateFormAction()->hidden();
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()->hidden();
    }

    public function form(Schema $schema): Schema
    {
        return PosCreateOrderForm::configure($schema);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Show success notification with "Cetak Struk" action.
     */
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $order = $this->record;
        $printUrl = route('admin.orders.invoice', $order->order_id);

        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Pembayaran berhasil diterima')
            ->body("Invoice {$order->kode_order} telah dibuat.")
            ->duration(10000)
            ->actions([
                \Filament\Actions\Action::make('print')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url($printUrl, shouldOpenInNewTab: true),

                \Filament\Actions\Action::make('close')
                    ->label('Tutup')
                    ->color('gray')
                    ->close(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dibuat_oleh_id'] = Filament::auth()->id();
        $data['sumber'] = $data['sumber'] ?? 'offline';
        $data['status'] = ($data['payment_method'] ?? null) === 'cash' ? 'sudah_dibayar' : ($data['status'] ?? 'menunggu_pembayaran');
        $data['order_type'] = $this->inferOrderType($data['items'] ?? []);

        // DEBUG
        \Illuminate\Support\Facades\Log::info('POS CreateOrder mutateFormDataBeforeCreate', [
            'items_count' => count($data['items'] ?? []),
            'item_types' => collect($data['items'] ?? [])->pluck('item_type')->all(),
            'inferred_order_type' => $data['order_type'],
        ]);

        if (empty($data['kode_order'])) {
            $data['kode_order'] = \App\Support\OrderCodeGenerator::next();
        }

        if ($data['status'] === 'sudah_dibayar' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Safety net: re-infer order_type after items relation is saved
        $this->record->loadMissing('items');
        $orderType = $this->inferOrderType(
            $this->record->items->map(fn ($i) => ['item_type' => $i->item_type])->toArray()
        );

        \Illuminate\Support\Facades\Log::info('POS CreateOrder afterCreate', [
            'order_id' => $this->record->order_id,
            'items_from_db' => $this->record->items->pluck('item_type')->all(),
            'final_order_type' => $orderType,
        ]);

        if ($this->record->order_type !== $orderType) {
            $this->record->update(['order_type' => $orderType]);
        }

        // Deduct product stock if the order reserves stock (pending / paid).
        app(\App\Services\Orders\ProductStockService::class)->deductForOrder($this->record);
    }

    /**
     * Infer order_type from cart items:
     * - Single item_type → use that type (mapped to DB value).
     * - Multiple types → 'campuran'.
     */
    private function inferOrderType(array $items): string
    {
        $types = collect($items)
            ->pluck('item_type')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (count($types) !== 1) {
            return 'campuran';
        }

        return match ($types[0]) {
            'product'    => 'produk',
            'treatment'  => 'treatment',
            'bundle'     => 'bundle',
            'konsultasi' => 'konsultasi',
            'membership' => 'membership',
            default      => 'campuran',
        };
    }
}
