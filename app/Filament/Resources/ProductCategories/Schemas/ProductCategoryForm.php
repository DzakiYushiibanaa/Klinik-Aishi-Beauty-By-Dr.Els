<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
            ->label('Nama Kategori')
            ->required()
            ->maxLength(255),

        Textarea::make('deskripsi')
            ->label('Deskripsi')
            ->rows(4)
            ->columnSpanFull(),

        Toggle::make('is_active')
            ->label('Aktif')
            ->default(true),
            ]);
    }
}
