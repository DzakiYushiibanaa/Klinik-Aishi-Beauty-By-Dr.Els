<?php

namespace App\Filament\Resources\TreatmentCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class TreatmentCategoryForm
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

        Toggle::make('doctor_allowed')
            ->label('Bisa Ditangani Oleh Dokter')
            ->default(true),

        Toggle::make('therapist_allowed')
            ->label('Bisa Ditangani Oleh Terapis')
            ->default(true),

        Toggle::make('is_active')
            ->label('Aktif')
            ->default(true),
            ]);
    }
}
