<?php

namespace App\Filament\Resources\Bundles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')->label('Kode Bundle')->required()->maxLength(100),
                TextInput::make('nama')->label('Nama Bundle')->required()->maxLength(255),

                Select::make('jenis')
                    ->label('Jenis Bundle')
                    ->options([
                        'product' => 'Produk',
                        'treatment' => 'Treatment',
                        'mixed' => 'Campuran',
                    ])
                    ->required(),

                Textarea::make('deskripsi')->label('Deskripsi')->rows(4)->columnSpanFull(),

                FileUpload::make('foto_path')
                    ->label('Foto Bundle')
                    ->disk('public')
                    ->directory('bundles')
                    ->image()
                    ->maxSize(2048),

                TextInput::make('harga_normal')->label('Harga Normal (Rp)')->numeric()->default(0),
                TextInput::make('harga_promo')->label('Harga Promo (Rp)')->numeric()->default(0),

                Toggle::make('is_member_only')->label('Khusus Member')->default(false),
                Toggle::make('is_active')->label('Aktif')->default(true),

                Repeater::make('productItems')
                    ->label('Isi Bundle (Produk)')
                    ->relationship('productItems')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('qty')->label('Qty')->numeric()->default(1)->required(),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Repeater::make('treatmentItems')
                    ->label('Isi Bundle (Treatment)')
                    ->relationship('treatmentItems')
                    ->schema([
                        Select::make('treatment_id')
                            ->label('Treatment')
                            ->relationship('treatment', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('qty')->label('Qty')->numeric()->default(1)->required(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
