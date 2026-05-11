<?php

namespace App\Filament\Resources\Treatments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TreatmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Info Layanan')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('treatment_category_id')
                                ->label('Kategori')
                                ->relationship('category', 'nama')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->label('Nama Kategori')
                                        ->placeholder('e.g., Facial')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('deskripsi')
                                        ->label('Deskripsi')
                                        ->placeholder('Opsional...')
                                        ->maxLength(65535),
                                    Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true),
                                ])
                                ->createOptionModalHeading('Tambah Kategori Baru')
                                ->createOptionAction(function (\Filament\Actions\Action $action) {
                                    return $action
                                        ->modalSubmitActionLabel('Simpan Kategori')
                                        ->modalCancelActionLabel('Cancel');
                                }),

                            TextInput::make('nama')
                                ->label('Nama Treatment')
                                ->placeholder('e.g., Gold Facial')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi treatment...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Operasional & Harga')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('harga')
                                ->label('Harga (Rp)')
                                ->numeric()
                                ->required(),

                            TextInput::make('durasi_menit')
                                ->label('Durasi (Menit)')
                                ->numeric()
                                ->default(0),
                        ]),
                        Select::make('min_level')
                            ->label('Minimal Level Terapis')
                            ->options([
                                1 => 'Junior',
                                2 => 'Senior',
                            ])
                            ->default(1)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Media & Status')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('foto_path')
                                ->label('Foto')
                                ->disk('public')
                                ->directory('treatments')
                                ->image()
                                ->maxSize(2048),

                            Group::make([
                                Placeholder::make('status_heading')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<div style="font-weight: 600; font-size: 0.875rem; color: #374151; margin-bottom: 0.5rem;">Status</div>')),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->inline()
                                    ->helperText('Jika OFF, treatment ini tidak bisa dipilih oleh pasien.'),
                            ])->columnSpan(1),
                        ]),
                    ]),
            ]);
    }
}
