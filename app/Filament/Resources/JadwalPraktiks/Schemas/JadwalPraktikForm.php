<?php

namespace App\Filament\Resources\JadwalPraktiks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class JadwalPraktikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Tenaga Medis (Dokter/Terapis)')
                    ->relationship(
                        name: 'tenagaMedis',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->role(['dokter', 'terapis'])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('jenis_layanan')
                    ->label('Jenis Layanan')
                    ->options([
                        'konsultasi' => 'Konsultasi',
                        'treatment' => 'Treatment',
                    ])
                    ->required(),

                Select::make('hari')
                    ->label('Hari')
                    ->options([
                        'senin' => 'Senin',
                        'selasa' => 'Selasa',
                        'rabu' => 'Rabu',
                        'kamis' => 'Kamis',
                        'jumat' => 'Jumat',
                        'sabtu' => 'Sabtu',
                        'minggu' => 'Minggu',
                    ])
                    ->required(),

                TimePicker::make('jam_mulai')->label('Jam Mulai')->required(),
                TimePicker::make('jam_selesai')->label('Jam Selesai')->required(),

                TextInput::make('durasi_slot_menit')
                    ->label('Durasi Slot (menit)')
                    ->numeric()
                    ->default(60)
                    ->required(),

                Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }
}
