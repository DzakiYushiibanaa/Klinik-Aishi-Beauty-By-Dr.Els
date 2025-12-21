<?php

namespace App\Filament\Resources\Treatments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TreatmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Treatment')
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

                Select::make('treatment_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('durasi_menit')
                    ->label('Durasi (menit)')
                    ->numeric()
                    ->default(0),

                Select::make('min_level')
                    ->label('Minimal Level Terapis')
                    ->options([
                        1 => 'Junior',
                        2 => 'Senior',
                    ])
                    ->default(1)
                    ->required()
                    ->helperText('Jika treatment memerlukan terapis Senior, pilih Senior.'),

                FileUpload::make('foto_path')
                    ->label('Foto Treatment')
                    ->disk('public')
                    ->directory('treatments')
                    ->image()
                    ->maxSize(2048),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
