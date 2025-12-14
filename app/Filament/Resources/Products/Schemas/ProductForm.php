<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')
            ->label('Kategori')
            ->relationship('category', 'nama')
            ->searchable()
            ->preload()
            ->required(),

        TextInput::make('nama')
            ->label('Nama Produk')
            ->required()
            ->maxLength(255),

        Textarea::make('deskripsi')
            ->label('Deskripsi')
            ->rows(5)
            ->columnSpanFull(),

        TextInput::make('harga')
            ->label('Harga (Rp)')
            ->numeric()
            ->required(),

        TextInput::make('berat_gram')
            ->label('Berat (gram)')
            ->numeric()
            ->default(0),

        TextInput::make('stok')
            ->label('Stok')
            ->numeric()
            ->default(0),

        DatePicker::make('tanggal_kadaluarsa')
            ->label('Tanggal Kadaluarsa'),

        FileUpload::make('foto_path')
            ->label('Foto Produk')
            ->disk('public')
            ->directory('products')
            ->image()
            ->imagePreviewHeight('150')
            ->maxSize(2048),

        Toggle::make('is_active')
            ->label('Aktif')
            ->default(true),
            ]);
    }
}
