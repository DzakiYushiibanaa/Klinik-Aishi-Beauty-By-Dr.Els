<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

class LayananPetugasSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 2: Layanan & Petugas Utama
                // ═══════════════════════════════════════════
                Section::make('Layanan & Petugas Utama')
                    ->schema([
                        ToggleButtons::make('jenis')
                            ->label('Jenis Layanan')
                            ->options([
                                'konsultasi' => 'Konsultasi',
                                'treatment' => 'Treatment',
                            ])
                            ->inline()
                            ->grouped()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('dokter_id', null);
                                $set('terapis_id', null);
                                $set('tanggal_jam', null);
                                $set('new_tanggal', null);
                                $set('new_jam', null);
                                $set('items', []);
                            }),

                        Select::make('dokter_id')
                            ->label('Pilih Dokter')
                            ->relationship(
                                name: 'dokter',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) =>
                                $query->whereHas('roles', fn(Builder $q) => $q->where('name', 'dokter'))
                                    ->whereHas('dokterProfile', fn($q) => $q->whereIn('status_praktik', ['aktif', 'Aktif']))
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn(Get $get) => $get('jenis') === 'konsultasi')
                            ->visible(fn(Get $get) => $get('jenis') === 'konsultasi')
                            ->reactive()
                            ->afterStateUpdated(fn($state, $set) => [
                                $set('tanggal_jam', null),
                                $set('new_tanggal', null),
                                $set('new_jam', null)
                            ]),
                    ])->columns(2);
    }
}
