<?php

namespace App\Filament\Resources\PatientForms\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PatientFormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Jenis Form')
                ->schema([
                Select::make('user_id')
                ->label('Pasien')
                ->relationship(
                    name: 'pasien',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                        'roles',
                        fn (Builder $q) => $q->where('name', 'pasien')
                    )
                )
                ->searchable()
                ->preload()
                ->required(),

                Select::make('tipe_form')
                    ->label('Tipe Form')
                    ->options([
                        'pra_konsul' => 'Pra Konsultasi',
                        'registrasi_member' => 'Registrasi Member',
                        'offline_intake' => 'Offline Intake',
                    ])
                    ->required()
                    ->default('pra_konsul'),
            ])->columns(2),
            Section::make('Data Kesehatan Dasar')
                ->schema([
                Select::make('golongan_darah')
                        ->options(['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O', 'TIDAK_TAHU' => 'Tidak tahu'])
                        ->default('TidakTahu')
                        ->rule('in:A,B,AB,O,TIDAK_TAHU')
                        ->required(),
                Textarea::make('riwayat_penyakit')
                    ->label('Riwayat Penyakit')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Data Kulit & Skincare')
                ->schema([
                Select::make('jenis_kulit')
                        ->label('Jenis Kulit')
                        ->options([
                            'Normal' => 'Normal',
                            'Kering' => 'Kering',
                            'Berminyak' => 'Berminyak',
                            'Kombinasi' => 'Kombinasi',
                            'Sensitif' => 'Sensitif',
                        ])
                        ->rule('in:Normal,Kering,Berminyak,Kombinasi,Sensitif')
                        ->required(),
                TextInput::make('periode_skincare_bulan')
                    ->label('Periode Skincare (bulan)')
                    ->numeric(),
                Textarea::make('skincare_sebelumnya')
                    ->label('Skincare Sebelumnya')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Riwayat Treatment')
                ->schema([
                Toggle::make('pernah_facial')->label('Pernah Facial')->default(false),
                Toggle::make('pernah_skin_booster')->label('Pernah Skin Booster')->default(false),
                Toggle::make('pernah_laser')->label('Pernah Laser')->default(false),
                Toggle::make('pernah_inject_infus')->label('Pernah Inject/Infus')->default(false),
            ])->columns(4),
            Section::make('Keluhan & Foto')
            ->schema([
                Textarea::make('keluhan_utama')
                    ->label('Keluhan Utama')
                    ->rows(4)
                    ->columnSpanFull(),

                FileUpload::make('foto_depan_path')
                    ->label('Foto Depan')
                    ->disk('public')
                    ->directory('pra-konsul')
                    ->image()
                    ->maxSize(2048),

                FileUpload::make('foto_kiri_path')
                    ->label('Foto Kiri')
                    ->disk('public')
                    ->directory('pra-konsul')
                    ->image()
                    ->maxSize(2048),

                FileUpload::make('foto_kanan_path')
                    ->label('Foto Kanan')
                    ->disk('public')
                    ->directory('pra-konsul')
                    ->image()
                    ->maxSize(2048),
            ])->columns(3),
            Section::make('Review Dokter')
            ->schema([
                Select::make('dokter_id')
                    ->label('Dokter Reviewer')
                    ->relationship(
                        name: 'dokter',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'roles',
                            fn (Builder $q) => $q->where('name', 'dokter')
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'baru' => 'Baru',
                        'ditinjau' => 'Ditinjau',
                        'direkomendasikan' => 'Direkomendasikan',
                        'ditutup' => 'Ditutup',
                    ])
                    ->rule('in:baru,ditinjau,direkomendasikan,ditutup')
                    ->required()
                    ->default('baru'),

                Textarea::make('rekomendasi_dokter')
                    ->label('Rekomendasi Dokter')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->visible(function () {
                /** @var User|null $user */
                $user = Filament::auth()->user();

                return !$user?->hasRole('pasien');
            }) // pasien gak lihat section ini
            ->columns(2),
            ]);
    }
}
