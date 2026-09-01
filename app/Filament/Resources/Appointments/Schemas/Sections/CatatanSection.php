<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class CatatanSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 3: Status dan Catatan
                // ═══════════════════════════════════════════
                Section::make('Catatan')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->label('Status Jadwal')
                            ->native(false)
                            ->placeholder('')
                            ->options([
                                'disetujui' => 'Disetujui',
                                'batal' => 'Dibatalkan',
                                'selesai' => 'Selesai',
                                'tidak_hadir' => 'Tidak Hadir',
                            ])
                            ->required()
                            ->default('disetujui')
                            ->disabled(fn() => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->hiddenOn('create')
                            ->columnSpanFull(),

                        Textarea::make('catatan_klinik')
                            ->label('Catatan')
                            ->placeholder('Instruksi internal atau catatan khusus untuk staf klinik (Pasien tidak dapat melihat ini).')
                            ->rows(3)
                            ->disabled(fn() => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->columnSpanFull(),

                        Hidden::make('dibuat_oleh_id')
                            ->default(fn() => Filament::auth()->id())
                            ->dehydrated(true),


                    ]);
    }
}
