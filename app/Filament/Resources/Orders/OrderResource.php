<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\Promotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'kode_order';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pasien.pasienProfile',
                'items.product',
                'items.treatment',
                'items.bundle',
                'promotion',
            ]);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::applyTotals($data);
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        return self::applyTotals($data);
    }

    private static function applyTotals(array $data): array
    {
        $items = $data['items'] ?? [];
        $subtotal = 0;

        foreach ($items as $it) {
            $subtotal += (int) ($it['subtotal'] ?? 0);
        }

        $diskonTotal = 0;

        if (!empty($data['promotion_id'])) {
            $promo = Promotion::find($data['promotion_id']);
            if ($promo && $promo->is_active) {
                $today = now()->startOfDay();

                if ($promo->start_date && $today->lt(\Carbon\Carbon::parse($promo->start_date)->startOfDay())) {
                    // no discount
                } elseif ($promo->end_date && $today->gt(\Carbon\Carbon::parse($promo->end_date)->endOfDay())) {
                    // no discount
                } elseif ((int)($promo->min_subtotal ?? 0) <= $subtotal) {
                    if (!empty($promo->diskon_nominal)) {
                        $diskonTotal = (int) $promo->diskon_nominal;
                    } elseif (!empty($promo->diskon_persen)) {
                        $diskonTotal = (int) floor($subtotal * ((int)$promo->diskon_persen / 100));
                    }
                    $diskonTotal = max(0, min($diskonTotal, $subtotal));
                }
            }
        }

        $ongkir = (int) ($data['ongkir'] ?? 0);
        $grandTotal = max(0, ($subtotal - $diskonTotal) + $ongkir);

        $data['subtotal'] = $subtotal;
        $data['diskon_total'] = $diskonTotal;
        $data['grand_total'] = $grandTotal;

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Transaksi';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
