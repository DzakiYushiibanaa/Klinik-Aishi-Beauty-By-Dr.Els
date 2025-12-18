<?php

namespace App\Filament\Resources\Dokters\Schemas;

use App\Models\Dokter;
use App\Support\CodeGenerator;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class DokterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Login (Users)')
                ->columns(2)
                ->schema([
                    TextInput::make('user_name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('user_email')
                        ->label('Email')
                        ->required()
                        ->email()
                        ->maxLength(255)
                        ->rules([
                            fn (?Dokter $record) => Rule::unique('users', 'email')
                                ->ignore($record?->user_id),
                        ]),

                    TextInput::make('user_phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(13),


                    TextInput::make('user_password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->minLength(8)
                        ->dehydrated(fn (string $operation) => $operation === 'create'),
                    TextInput::make('user_password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->same('user_password')
                        ->dehydrated(false),
                ]),
                Section::make('Profil Dokter')
                ->columns(2)
                ->schema([
                    TextInput::make('kode_dokter')
                        ->label('Kode Dokter')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => CodeGenerator::monthly(new \App\Models\Dokter(), 'kode_dokter', 'DR'))
                        ->unique(table: Dokter::class, column: 'kode_dokter', ignoreRecord: true),

                    TextInput::make('spesialis')
                        ->label('Spesialis')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('pengalaman_tahun')
                        ->label('Pengalaman (Tahun)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(60)
                        ->nullable(),

                    Select::make('status_praktik')
                        ->label('Status Praktik')
                        ->options([
                            'aktif' => 'Aktif',
                            'nonaktif' => 'Nonaktif',
                        ])
                        ->default('aktif')
                        ->required(),

                    FileUpload::make('foto_profil_path')
                        ->label('Foto Profil')
                        ->disk('public')
                        ->directory('dokter')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
