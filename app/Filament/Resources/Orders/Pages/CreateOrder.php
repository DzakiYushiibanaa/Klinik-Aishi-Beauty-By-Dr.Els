<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dibuat_oleh_id'] = Filament::auth()->id();

        // kode order: ORD-YYYYMMDD-0001
        $today = now()->format('Ymd');
        $prefix = "ORD-{$today}-";
        $last = Order::where('kode_order', 'like', "{$prefix}%")
            ->orderBy('kode_order', 'desc')
            ->value('kode_order');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        $data['kode_order'] = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        // hitung total dari items
        [$subtotal, $totalWeight] = $this->calcSubtotalAndWeight($data['items'] ?? []);
        $diskonTotal = OrderForm::calcPromoDiscount($data['promotion_id'] ?? null, $subtotal);
        $ongkir = (int) ($data['ongkir'] ?? 0);
        $data['subtotal'] = $subtotal;
        $data['diskon_total'] = $diskonTotal;
        $data['grand_total'] = max(0, ($subtotal - $diskonTotal) + $ongkir);
        $data['order_type'] = OrderForm::inferOrderType($data['items'] ?? []);
        $data['total_weight_gram'] = $totalWeight;

        if (($data['status'] ?? 'draft') === 'lunas' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return OrderForm::applyTotalsFromData($data);
    }

    private function calcSubtotalAndWeight(array $items): array
    {
        $subtotal = 0;
        $weight = 0;

        foreach ($items as $it) {
            $subtotal += (int) ($it['subtotal'] ?? 0);

            if (($it['item_type'] ?? null) === 'product' && !empty($it['product_id'])) {
                $p = Product::find($it['product_id']);
                if ($p) {
                    $weight += ((int) ($p->berat_gram ?? 0)) * (int) ($it['qty'] ?? 1);
                }
            }
        }

        return [$subtotal, $weight];
    }
}
