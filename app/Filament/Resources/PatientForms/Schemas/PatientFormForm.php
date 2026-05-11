<?php

namespace App\Filament\Resources\PatientForms\Schemas;

use App\Models\Pasien;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
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
                Section::make('Identitas dan Sumber Form')
                    ->schema([
                        Select::make('pasien_id')
                            ->label('Pasien')
                            ->relationship(name: 'pasien', titleAttribute: 'nama')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nama ?? ('Pasien #' . $record->pasien_id))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nama')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->nullable()
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                    ->nullable()
                                    ->native(false),
                                TextInput::make('no_telepon')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->maxLength(15)
                                    ->nullable(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $rm = \App\Support\CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', 'NM', 6, '-');
                                $pasien = Pasien::create([
                                    'nama' => $data['nama'],
                                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                                    'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                                    'no_telepon' => $data['no_telepon'] ?? null,
                                    'no_rekam_medis' => $rm,
                                    'is_member' => false,
                                ]);
                                return $pasien->getKey();
                            }),

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

                Section::make('Keluhan dan Bukti Foto')
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

                Section::make('Data Kesehatan dan Kondisi Kulit')
                    ->schema([
                        Select::make('golongan_darah')
                            ->options(['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O', 'TIDAK_TAHU' => 'Tidak tahu'])
                            ->default('TidakTahu')
                            ->rule('in:A,B,AB,O,TIDAK_TAHU')
                            ->required(),

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

                        Textarea::make('riwayat_penyakit')
                            ->label('Riwayat Penyakit')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Riwayat Skincare dan Treatment')
                    ->schema([
                        TextInput::make('periode_skincare_bulan')
                            ->label('Periode Pakai')
                            ->placeholder('Mis: 6 Bulan terakhir')
                            ->columnSpan(['default' => 1]),
                            
                        TextInput::make('skincare_sebelumnya')
                            ->label('Skincare Sebelumnya')
                            ->placeholder('Sebutkan brand/tipe...')
                            ->columnSpan(['default' => 1]),

                        Toggle::make('pernah_facial')->label('Pernah Facial')->default(false),
                        Toggle::make('pernah_skin_booster')->label('Skin Booster')->default(false),
                        Toggle::make('pernah_laser')->label('Laser')->default(false),
                        Toggle::make('pernah_inject_infus')->label('Inject / Infus')->default(false),
                    ])->columns(2),

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
                                )->whereHas('dokterProfile', fn($q) => $q->whereIn('status_praktik', ['aktif', 'Aktif']))
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
                        /** @var \App\Models\User|null $user */
                        $user = Filament::auth()->user();
                        return !$user?->hasRole('pasien');
                    })
                    ->columns(2),
            ]);
    }
}
