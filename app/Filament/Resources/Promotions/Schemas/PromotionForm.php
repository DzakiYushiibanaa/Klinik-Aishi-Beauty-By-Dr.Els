<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')->label('Kode Promo')->required(),
                TextInput::make('nama')->label('Nama Promo')->required(),
                Textarea::make('deskripsi')->label('Deskripsi')->columnSpanFull(),

                Select::make('jenis')
                    ->label('Jenis Promo')
                    ->options([
                        'order' => 'Order',
                        'treatment' => 'Treatment',

                        'product' => 'Produk',
                    ])->required(),

                TextInput::make('min_subtotal')->label('Minimal Subtotal')->numeric()->default(0),

                TextInput::make('diskon_nominal')->label('Diskon Nominal (Rp)')->numeric()->nullable(),
                TextInput::make('diskon_persen')->label('Diskon Persen (%)')->numeric()->nullable(),

                DatePicker::make('start_date')->label('Mulai')->required(),
                DatePicker::make('end_date')->label('Berakhir')->required(),

                Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }
}
