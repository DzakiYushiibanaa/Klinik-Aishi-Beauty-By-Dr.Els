<?php

namespace App\Filament\Resources\Orders\Tables\Concerns;

use App\Models\Order;
use App\Notifications\OrderShippingUpdated;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Utilities\Get;

trait HasOrderHelpers
{
    private static function calculateGrandTotal(Order $order, Get $get): int
    {
        $cap    = self::calculateBillingCap($order);
        $diskon = max(0, min((int) ($get('diskon_total') ?? $order->diskon_total ?? 0), $cap));

        return max(0, $cap - $diskon);
    }

    private static function calculateBillingCap(Order $order): int
    {
        $subtotal = (int) ($order->subtotal ?? 0);
        $ongkir   = self::orderHasShipping($order) ? (int) ($order->ongkir ?? 0) : 0;
        return $subtotal + $ongkir;
    }

    private static function orderHasShipping(Order $order): bool
    {
        $order->loadMissing('items');

        if (! self::isProductOnlyOrder($order)) {
            return false;
        }

        if (! empty($order->shipping_method)) return true;
        if (! empty($order->shipping_address)) return true;
        if ((int) ($order->ongkir ?? 0) > 0) return true;

        return false;
    }

    private static function isProductOnlyOrder(Order $order): bool
    {
        $order->loadMissing('items');

        return $order->items->isNotEmpty()
            && $order->items->every(fn ($item): bool => ! empty($item->product_id) || $item->item_type === 'product');
    }

    /**
     * 🚚 Update Resi Pengiriman — set waybill + change shipping_status to 'dikirim'.
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
