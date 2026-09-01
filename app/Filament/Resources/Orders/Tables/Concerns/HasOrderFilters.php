<?php

namespace App\Filament\Resources\Orders\Tables\Concerns;

use Filament\Tables\Filters\SelectFilter;

trait HasOrderFilters
{
    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status Pembayaran')
                ->options([
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'cancel_requested'    => 'Menunggu Konfirmasi Pembatalan',
                    'sudah_dibayar'       => 'Sudah Dibayar',
                    'batal'               => 'Batal',
                    'draft'               => 'Draft',
                ]),

            SelectFilter::make('shipping_status')
                ->label('Status Pengiriman')
                ->options([
                    'belum_dikirim' => 'Belum Dikirim',
                    'diproses'      => 'Sedang Diproses',
                    'dikemas'       => 'Dikemas',
                    'dikirim'       => 'Dikirim',
                    'selesai'       => 'Selesai',
                    'dibatalkan'    => 'Dibatalkan',
                ]),
        ];
    }

}
