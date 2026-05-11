<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Full-page "Transaksi Baru" — navigate to /create instead of slide-over
            CreateAction::make()
                ->label('New Transaksi')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kelola riwayat transaksi dan pembayaran klinik Aishi Beauty.';
    }
}
