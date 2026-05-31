<?php

namespace App\Filament\Resources\RekamMedis\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RekamMedisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('no_kunjungan')
                    ->label('No. Kunjungan')
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('appointment_id')
                    ->label('Appointment ID')
                    ->disabled()
                    ->dehydrated(true),

                Select::make('pasien_id')
                    ->label('Pasien')
                    ->relationship(
                        name: 'pasien',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->role('pasien')->whereHas('pasienProfile')
                    )
                    ->disabled()
                    ->dehydrated(true),

                Select::make('dokter_id')
                    ->label('Dokter')
                    ->relationship(
                        name: 'dokter',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->role('dokter')->whereHas('dokterProfile', fn($q) => $q->whereIn('status_praktik', ['aktif', 'Aktif']))
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('terapis_id')
                    ->label('Terapis')
                    ->relationship(
                        name: 'terapis',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->role('terapis')->whereHas('terapisProfile')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Textarea::make('keluhan')
                    ->label('Keluhan')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('diagnosa')
                    ->label('Diagnosa')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('tindakan')
                    ->label('Tindakan')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('foto_sebelum_path')
                    ->label('Foto Sebelum')
                    ->disk('public')
                    ->directory('rekam-medis/sebelum')
                    ->image()
                    ->imageEditor()
                    ->nullable(),

                FileUpload::make('foto_sesudah_path')
                    ->label('Foto Sesudah')
                    ->disk('public')
                    ->directory('rekam-medis/sesudah')
                    ->image()
                    ->imageEditor()
                    ->nullable(),

                Textarea::make('produk_rekomendasi')
                    ->label('Produk Rekomendasi')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('catatan_lanjutan')
                    ->label('Catatan Lanjutan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
