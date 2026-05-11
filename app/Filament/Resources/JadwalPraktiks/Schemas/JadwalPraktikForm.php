<?php

namespace App\Filament\Resources\JadwalPraktiks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class JadwalPraktikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('user_id')
                    ->label('Tenaga Medis')
                    ->relationship(
                        name: 'tenagaMedis',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->role(['dokter', 'terapis'])
                            ->where(function (Builder $q) {
                                $q->whereHas('dokterProfile', fn (Builder $dq) =>
                                    $dq->whereIn('status_praktik', ['aktif', 'Aktif'])
                                )->orWhereHas('terapisProfile', fn (Builder $tq) =>
                                    $tq->whereIn('status_kerja', ['aktif', 'Aktif'])
                                );
                            })
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

                TextInput::make('durasi_slot_menit')
                    ->label('Durasi Slot (menit)')
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(480)
                    ->default(60)
                    ->required(),

                TimePicker::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->seconds(false)
                    ->default('09:00')
                    ->required(),

                TimePicker::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->default('18:30')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Enable this schedule immediately')
                    ->default(true)
                    ->inline()
                    ->columnSpanFull(),
            ]);
    }
}
