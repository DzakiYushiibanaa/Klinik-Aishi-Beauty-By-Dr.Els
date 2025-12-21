<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // hitung ulang total setiap edit (biar selalu konsisten)
        [$subtotal, $totalWeight] = $this->calcSubtotalAndWeight($data['items'] ?? []);
        $diskonTotal = OrderForm::calcPromoDiscount($data['promotion_id'] ?? null, $subtotal);
        $ongkir = (int) ($data['ongkir'] ?? 0);

        $data['subtotal'] = $subtotal;
        $data['diskon_total'] = $diskonTotal;
        $data['grand_total'] = max(0, ($subtotal - $diskonTotal) + $ongkir);
        $data['order_type'] = OrderForm::inferOrderType($data['items'] ?? []);
        $data['total_weight_gram'] = $totalWeight;

        // paid_at otomatis kalau status lunas
        if (($data['status'] ?? null) === 'lunas' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        // kalau status dibatalkan/draft, kamu bisa pilih mau null-in paid_at atau tidak
        // kalau mau:
        // if (($data['status'] ?? null) !== 'lunas') $data['paid_at'] = null;

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
