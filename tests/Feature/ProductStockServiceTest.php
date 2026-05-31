<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\User;
use App\Services\Orders\ProductStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductStockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_returns_product_stock_for_cancelled_order(): void
    {
        $user = User::factory()->create();
        $categoryId = DB::table('product_categories')->insertGetId([
            'nama' => 'Skincare',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'product_category_id' => $categoryId,
            'nama' => 'Facial Foam Acne Care',
            'harga' => 100000,
            'stok' => 8,
        ]);

        $order = $this->createOrder($user, true);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'item_type' => 'product',
            'description' => $product->nama,
            'qty' => 2,
            'harga_satuan' => 100000,
            'subtotal' => 200000,
        ]);

        app(ProductStockService::class)->restoreForOrder($order);

        $this->assertSame(10, $product->fresh()->stok);
        $this->assertFalse($order->fresh()->is_stock_deducted);
    }

    public function test_restore_skips_treatment_items(): void
    {
        $user = User::factory()->create();
        $categoryId = DB::table('treatment_categories')->insertGetId([
            'nama' => 'Facial',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $treatment = Treatment::create([
            'treatment_category_id' => $categoryId,
            'nama' => 'Acne Treatment',
            'harga' => 150000,
            'durasi_menit' => 60,
        ]);

        $order = $this->createOrder($user, true);

        OrderItem::create([
            'order_id' => $order->order_id,
            'treatment_id' => $treatment->treatment_id,
            'item_type' => 'treatment',
            'description' => $treatment->nama,
            'qty' => 1,
            'harga_satuan' => 150000,
            'subtotal' => 150000,
        ]);

        app(ProductStockService::class)->restoreForOrder($order);

        $this->assertFalse($order->fresh()->is_stock_deducted);
    }

    public function test_restore_is_idempotent(): void
    {
        $user = User::factory()->create();
        $categoryId = DB::table('product_categories')->insertGetId([
            'nama' => 'Skincare',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'product_category_id' => $categoryId,
            'nama' => 'UV Protect White',
            'harga' => 120000,
            'stok' => 9,
        ]);

        $order = $this->createOrder($user, true);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'item_type' => 'product',
            'description' => $product->nama,
            'qty' => 1,
            'harga_satuan' => 120000,
            'subtotal' => 120000,
        ]);

        $service = app(ProductStockService::class);
        $service->restoreForOrder($order);
        $service->restoreForOrder($order->fresh());

        $this->assertSame(10, $product->fresh()->stok);
        $this->assertFalse($order->fresh()->is_stock_deducted);
    }

    private function createOrder(User $user, bool $isStockDeducted): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'dibuat_oleh_id' => $user->id,
            'kode_order' => 'ORD-TEST-' . uniqid(),
            'order_type' => 'produk',
            'sumber' => 'online',
            'status' => 'cancel_requested',
            'subtotal' => 0,
            'grand_total' => 0,
            'is_stock_deducted' => $isStockDeducted,
        ]);
    }
}
