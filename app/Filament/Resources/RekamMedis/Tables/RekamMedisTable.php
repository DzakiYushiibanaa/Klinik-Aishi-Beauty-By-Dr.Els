<?php

namespace App\Filament\Resources\RekamMedis\Tables;

use App\Models\RekamMedis;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RekamMedisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pasien.pasienProfile.no_rekam_medis')
                    ->label('No. RM')
                    ->placeholder('-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('pasien.pasienProfile', function ($q) use ($search) {
                            $q->where('no_rekam_medis', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('no_kunjungan')->label('No Kunjungan')->searchable()->sortable(),
                TextColumn::make('appointment_id')->label('Appointment')->sortable(),
                TextColumn::make('pasien.name')->label('Pasien')->searchable(),
                TextColumn::make('dokter.name')->label('Dokter')->searchable(),
                TextColumn::make('status_tindakan')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])->defaultSort('rekam_medis_id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
