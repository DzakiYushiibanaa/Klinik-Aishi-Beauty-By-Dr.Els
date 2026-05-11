<?php

namespace App\Filament\Resources\Pasiens\Schemas;

use App\Models\Pasien;
use App\Support\CodeGenerator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PasienForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // ===== Section: Identitas & RM =====
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(12)->schema([
                            // Kolom kiri: No. Rekam Medis
                            Placeholder::make('rm_preview')
                                ->hiddenLabel()
                                ->columnSpan(8)
                                ->content(function (Get $get): HtmlString {
                                    $rm = $get('no_rekam_medis') ?: '—';
                                    return new HtmlString('
                                        <div style="display:flex; flex-direction:column; gap:4px;">
                                            <span style="text-transform:uppercase; letter-spacing:0.08em; color:#8A8078; font-size:0.75rem; font-weight:500;">No. Rekam Medis</span>
                                            <span style="font-weight:700; font-size:1.25rem; color:#111827; letter-spacing:-0.01em;">'.e($rm).'</span>
                                        </div>
                                    ');
                                }),

                            // Kolom kanan: Member toggle
                            Toggle::make('is_member')
                                ->label('Member?')
                                ->inline()
                                ->default(false)
                                ->live()
                                ->columnSpan(4)
                                ->afterStateUpdated(function ($state, $set) {
                                    $set('member_since', $state ? now() : null);
                                    $prefix = $state ? 'RM' : 'NM';
                                    $set('no_rekam_medis', CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', $prefix, 6, '-'));
                                }),
                        ]),

                        // hidden field storage untuk no_rekam_medis
                        TextInput::make('no_rekam_medis')
                            ->hidden()
                            ->dehydrated(true)
                            ->default(fn (Get $get) => CodeGenerator::prefixNumber(
                                new Pasien(),
                                'no_rekam_medis',
                                $get('is_member') ? 'RM' : 'NM',
                                6,
                                '-'
                            ))
                            ->formatStateUsing(fn ($state, Get $get) => $state ?: CodeGenerator::prefixNumber(
                                new Pasien(),
                                'no_rekam_medis',
                                $get('is_member') ? 'RM' : 'NM',
                                6,
                                '-'
                            )),

                        Grid::make(2)->schema([
                            TextInput::make('nama')
                                ->label('Nama')
                                ->placeholder('Masukkan nama lengkap')
                                ->required()
                                ->maxLength(255),

                            DatePicker::make('tanggal_lahir')
                                ->label('Tanggal Lahir')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->nullable(),

                            Select::make('jenis_kelamin')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'L' => 'Laki-laki',
                                    'P' => 'Perempuan',
                                ])
                                ->native(false)
                                ->placeholder('')
                                ->nullable(),

                            TextInput::make('no_telepon')
                                ->label('No. Telepon')
                                ->tel()
                                ->placeholder('Contoh: 08123456789')
                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                ->maxLength(15)
                                ->nullable(),
                        ]),

                        DateTimePicker::make('member_since')
                            ->label('Member Sejak')
                            ->seconds(false)
                            ->native(false)
                            ->visible(fn (Get $get) => (bool) $get('is_member'))
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                // ===== Section: Alamat & Foto =====
                Section::make('Alamat & Foto')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('foto_profil_path')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->directory('pasien')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/gif'])
                            ->helperText('SVG, PNG, JPG atau GIF (Rekomendasi 800×800px)')
                            ->columnSpanFull(),

                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->placeholder('Masukkan alamat lengkap dengan nama jalan, RT/RW, no rumah...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('kota')
                                ->label('Kota / Kabupaten')
                                ->placeholder('Masukkan kota'),

                            TextInput::make('provinsi')
                                ->label('Provinsi')
                                ->placeholder('Masukkan provinsi'),
                        ]),

                        TextInput::make('kode_pos')
                            ->label('Kode Pos')
                            ->placeholder('Contoh: 12345')
                            ->maxLength(10),
                    ]),
            ]);
    }
}
