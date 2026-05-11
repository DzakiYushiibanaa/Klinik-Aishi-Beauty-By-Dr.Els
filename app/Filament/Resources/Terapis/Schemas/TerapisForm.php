<?php

namespace App\Filament\Resources\Terapis\Schemas;

use App\Models\Terapis;
use App\Support\CodeGenerator;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class TerapisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // ===== Section: Profil Terapis =====
                Section::make('Profil Terapis')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(12)->schema([
                            Placeholder::make('kode_terapis_preview')
                                ->hiddenLabel()
                                ->columnSpan(8)
                                ->content(function (Get $get): HtmlString {
                                    $kode = $get('kode_terapis') ?: '—';
                                    return new HtmlString('
                                        <div style="display:flex; flex-direction:column; gap:4px;">
                                            <span style="color:#8A8078; font-size:0.8125rem;">Kode Terapis</span>
                                            <span style="font-weight:700; font-size:1.25rem; color:#111827; letter-spacing:-0.01em;">'.e($kode).'</span>
                                        </div>
                                    ');
                                }),

                            Toggle::make('is_active_toggle')
                                ->label('Aktif')
                                ->inline()
                                ->columnSpan(4)
                                ->dehydrated(false)
                                ->live()
                                ->default(true)
                                ->formatStateUsing(fn (Get $get) => ($get('status_kerja') ?? 'aktif') === 'aktif')
                                ->afterStateUpdated(fn ($state, $set) =>
                                    $set('status_kerja', $state ? 'aktif' : 'nonaktif')
                                ),
                        ]),

                        // hidden storage fields
                        TextInput::make('kode_terapis')
                            ->hidden()
                            ->dehydrated(true)
                            ->default(fn () => CodeGenerator::monthly(new Terapis(), 'kode_terapis', 'TR'))
                            ->formatStateUsing(fn ($state) => $state ?: CodeGenerator::monthly(new Terapis(), 'kode_terapis', 'TR'))
                            ->unique(table: Terapis::class, column: 'kode_terapis', ignoreRecord: true),

                        TextInput::make('status_kerja')
                            ->hidden()
                            ->dehydrated(true)
                            ->default('aktif'),

                        FileUpload::make('foto_profil_path')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->directory('terapis')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/gif'])
                            ->helperText('SVG, PNG, JPG atau GIF (max. 800×400px)')
                            ->nullable()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('user_name')
                                ->label('Nama Lengkap')
                                ->placeholder('Masukkan nama')
                                ->required()
                                ->maxLength(255),

                            Select::make('jabatan')
                                ->label('Jabatan')
                                ->options([
                                    'Junior Beautician'   => 'Junior Beautician',
                                    'Senior Beautician'   => 'Senior Beautician',
                                    'Lead Physiotherapist'=> 'Lead Physiotherapist',
                                    'Massage Therapist'   => 'Massage Therapist',
                                    'Aesthetician'        => 'Aesthetician',
                                ])
                                ->native(false)
                                ->placeholder('')
                                ->required(),

                            TextInput::make('pengalaman_tahun')
                                ->label('Pengalaman')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(60)
                                ->default(0)
                                ->placeholder('0')
                                ->suffix('Tahun')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ===== Section: Akun Login & Keamanan =====
                Section::make('Akun Login & Keamanan')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('login_info_banner')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->content(fn (): HtmlString => new HtmlString('
                                <div style="display:flex; align-items:flex-start; gap:8px; padding:12px 14px; background:#FEF7E6; border:1px solid #F3E4C4; border-radius:10px; color:#7A5A3A; font-size:0.875rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0; margin-top:1px; color:#B8956F;">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Data ini digunakan terapis untuk login ke aplikasi.</span>
                                </div>
                            ')),

                        Grid::make(2)->schema([
                            TextInput::make('user_email')
                                ->label('Email')
                                ->placeholder('contoh@email.com')
                                ->required()
                                ->email()
                                ->maxLength(255)
                                ->rules([
                                    fn (?Terapis $record) => Rule::unique('users', 'email')
                                        ->ignore($record?->user_id),
                                ]),

                            TextInput::make('user_phone')
                                ->label('No. Telepon')
                                ->placeholder('08xx-xxxx-xxxx')
                                ->tel()
                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                ->maxLength(20),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('user_password')
                                ->label('Password')
                                ->placeholder('Minimal 8 karakter')
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation) => $operation === 'create')
                                ->minLength(8)
                                ->dehydrated(fn ($state) => filled($state)),

                            TextInput::make('user_password_confirmation')
                                ->label('Konfirmasi Password')
                                ->placeholder('Ulangi password')
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation) => $operation === 'create')
                                ->same('user_password')
                                ->dehydrated(false),
                        ]),
                    ]),
            ]);
    }
}
