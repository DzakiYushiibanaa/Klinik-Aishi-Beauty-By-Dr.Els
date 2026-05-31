<?php

namespace App\Services\Orders;

use App\Models\BundleProductItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles product stock deduction/restoration for orders.
 *
 * Deduction happens when an order status transitions to a "reserved" state
 * (menunggu_pembayaran, pending, sudah_dibayar). Restoration happens when
 * an order is cancelled after stock was previously deducted.
 */
class ProductStockService
{
    /** Statuses that "hold" stock (product is considered sold / reserved). */
    private const RESERVING_STATUSES = [
        'menunggu_pembayaran',
        'pending',
        'sudah_dibayar',
    ];

    /** Statuses that release reserved stock. */
    private const RELEASING_STATUSES = [
        'batal',
        'dibatalkan',
        'cancelled',
        'refund',
    ];

    /**
     * Deduct product stock for every product/bundle item in the order.
     * Safe to call multiple times — uses an `is_stock_deducted` flag on the order
     * to prevent double deduction.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::lockForUpdate()->find($order->getKey());

            if (!$lockedOrder || !$this->isReservingStatus($lockedOrder->status)) {
                return;
            }

            if ($lockedOrder->is_stock_deducted) {
                return;
            }

            $lockedOrder->loadMissing(['items.product', 'items.bundle.productItems']);

            foreach ($lockedOrder->items as $item) {
                $this->deductForItem($item);
            }

            $lockedOrder->update(['is_stock_deducted' => true]);
        });
    }

    /**
     * Restore stock for an order that was previously deducted.
     */
    public function restoreForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::lockForUpdate()->find($order->getKey());

            if (!$lockedOrder || !$lockedOrder->is_stock_deducted) {
                return;
            }

            $lockedOrder->loadMissing(['items.product']);

            foreach ($lockedOrder->items as $item) {
                $this->restoreForItem($lockedOrder, $item);
            }

            $lockedOrder->update(['is_stock_deducted' => false]);
        });
    }

    /**
     * Sync stock state based on a status transition (oldStatus → newStatus).
     */
    public function syncForStatusChange(Order $order, ?string $oldStatus, ?string $newStatus): void
    {
        $wasReserving = $oldStatus && $this->isReservingStatus($oldStatus);
        $isReserving  = $newStatus && $this->isReservingStatus($newStatus);

        if (!$wasReserving && $isReserving) {
            $this->deductForOrder($order);
            return;
        }

        if ($wasReserving && !$isReserving && in_array($newStatus, self::RELEASING_STATUSES, true)) {
            $this->restoreForOrder($order);
        }
    }

    // ═══════════════════════════════════════════════════════
    // INTERNAL
    // ═══════════════════════════════════════════════════════

    private function isReservingStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), self::RESERVING_STATUSES, true);
    }

    private function deductForItem(OrderItem $item): void
    {
        $qty = max(1, (int) $item->qty);

        if ($item->item_type === 'product' && $item->product_id) {
            $this->decrementProductStock((int) $item->product_id, $qty);
            return;
        }

        if ($item->item_type === 'bundle' && $item->bundle_id) {
            $this->decrementBundleProductsStock((int) $item->bundle_id, $qty);
        }
    }

    private function restoreForItem(Order $order, OrderItem $item): void
    {
        $qty = max(1, (int) $item->qty);

        if ($item->product_id) {
            $this->incrementProductStock((int) $item->product_id, $qty, $order);
        }
    }

    private function decrementProductStock(int $productId, int $qty): void
    {
        $product = Product::lockForUpdate()->find($productId);
        if (!$product) return;

        $newStock = max(0, (int) $product->stok - $qty);
        $product->update(['stok' => $newStock]);
    }

    private function incrementProductStock(int $productId, int $qty, ?Order $order = null): void
    {
        $product = Product::lockForUpdate()->find($productId);
        if (!$product) return;

        $stockBefore = (int) $product->stok;
        $stockAfter = $stockBefore + $qty;

        $product->update(['stok' => $stockAfter]);

        Log::info('[Cancel Approved]', [
            'order_id' => $order?->order_id,
            'order' => $order?->kode_order,
            'product_id' => $product->product_id,
            'product' => $product->nama,
            'qty_restore' => $qty,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ]);
    }

    /**
     * Bundle deduction — for every product in the bundle, deduct (itemQty × bundleQty).
     */
    private function decrementBundleProductsStock(int $bundleId, int $bundleQty): void
    {
        $bundleProducts = BundleProductItem::where('bundle_id', $bundleId)->get();

        foreach ($bundleProducts as $bp) {
            $productQty = (int) ($bp->qty ?? 1) * $bundleQty;
            $this->decrementProductStock((int) $bp->product_id, $productQty);
        }
    }

}
