<?php

namespace App\Filament\Resources\Pasiens\Schemas;

use App\Models\Pasien;
use App\Support\CodeGenerator;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PasienForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pasien')
                ->columns(2)
                ->schema([
                    TextInput::make('nama')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    \Filament\Forms\Components\DatePicker::make('tanggal_lahir')
                        ->label('Tanggal Lahir')
                        ->nullable()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    \Filament\Forms\Components\Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->nullable()
                        ->native(false),

                    TextInput::make('no_telepon')
                        ->label('No. Telepon')
                        ->tel()
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->maxLength(15)
                        ->nullable(),
                ]),

                Section::make('Profil Pasien')
                ->columns(2)
                ->schema([
                    TextInput::make('no_rekam_medis')
                        ->label('No. Rekam Medis')
                        ->disabled()
                        ->dehydrated(true)
                        ->reactive()
                        ->default(function (Get $get) {
                            $prefix = $get('is_member') ? 'RM' : 'NM';
                            return CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', $prefix, 6, '-');
                        })
                        ->helperText('Otomatis: NM untuk non-member, RM untuk member.')
                        ->unique(table: Pasien::class, column: 'no_rekam_medis', ignoreRecord: true),

                    Toggle::make('is_member')
                        ->label('Member?')
                        ->default(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            // set member_since
                            $set('member_since', $state ? now() : null);

                            // update preview kode (NM/RM) di form
                            $prefix = $state ? 'RM' : 'NM';
                            $set('no_rekam_medis', CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', $prefix, 6, '-'));
                        }),

                    DateTimePicker::make('member_since')
                        ->label('Member Sejak')
                        ->seconds(false)
                        ->native(false)
                        ->visible(fn ($get) => (bool) $get('is_member'))
                        ->nullable(),

                    FileUpload::make('foto_profil_path')
                        ->label('Foto Profil')
                        ->disk('public')
                        ->directory('pasien')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->nullable()
                        ->columnSpanFull(),

                    Textarea::make('alamat')
                        ->label('Alamat')
                        ->rows(3)
                        ->columnSpanFull()
                        ->nullable(),

                    TextInput::make('kota')->label('Kota')->nullable(),
                    TextInput::make('provinsi')->label('Provinsi')->nullable(),
                    TextInput::make('kode_pos')->label('Kode Pos')->nullable(),
                ]),
            ]);
    }
}
