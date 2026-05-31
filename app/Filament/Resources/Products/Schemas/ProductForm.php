<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Info Utama')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_category_id')
                                ->label('Kategori')
                                ->relationship('category', 'nama')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->label('Nama Kategori')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->createOptionModalHeading('Tambah Kategori Baru'),

                            TextInput::make('nama')
                                ->label('Nama Produk')
                                ->placeholder('Contoh: Radiance Serum')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Masukkan deskripsi produk...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Harga & Inventaris')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('harga')
                                ->label('Harga (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->required(),

                            TextInput::make('berat_gram')
                                ->label('Berat (gram)')
                                ->numeric()
                                ->default(0),

                            TextInput::make('stok')
                                ->label('Stok')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            DatePicker::make('tanggal_kadaluarsa')
                                ->label('Tanggal Kadaluarsa')
                                ->native(false)
                                ->displayFormat('d M Y'),
                        ]),
                    ]),

                Section::make('Media & Status')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('foto_path')
                            ->label('Foto Produk')
                            ->disk('public')
                            ->directory('products')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml'])
                            ->helperText('PNG, JPG, SVG maksimal 5MB')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Tampilkan produk ini di katalog produk')
                            ->default(true)
                            ->inline()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
