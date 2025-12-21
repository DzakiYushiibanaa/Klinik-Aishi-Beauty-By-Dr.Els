<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_order')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pasien.name')
                    ->label('Pasien')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pasien.pasienProfile.no_rekam_medis')
                    ->label('No. RM')
                    ->default('-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('pasien.pasienProfile', fn ($q) =>
                            $q->where('no_rekam_medis', 'like', "%{$search}%")
                        );
                    }),

                TextColumn::make('items_summary')
                    ->label('Item')
                    ->state(function (Order $record): string {
                        return $record->items->map(function ($item) {
                            $qty = (int) ($item->qty ?? 1);

                            if ($item->item_type === 'product' && $item->product) {
                                return $item->product->nama . ($qty > 1 ? " x{$qty}" : '');
                            }

                            if ($item->item_type === 'treatment' && $item->treatment) {
                                return $item->treatment->nama . ($qty > 1 ? " x{$qty}" : '');
                            }

                            if ($item->item_type === 'bundle' && $item->bundle) {
                                return 'Bundle: ' . $item->bundle->nama . ($qty > 1 ? " x{$qty}" : '');
                            }

                            return $item->description ?: $item->item_type;
                        })->filter()->implode(', ');
                    })
                    ->wrap()
                    ->limit(70),

                TextColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'menunggu_pembayaran',
                        'success' => 'lunas',
                        'danger' => 'batal',
                    ])
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->default(0)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Lunas Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])->defaultSort('order_id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
