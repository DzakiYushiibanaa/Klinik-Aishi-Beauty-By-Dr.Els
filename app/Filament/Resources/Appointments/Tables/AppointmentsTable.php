<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_id')->label('ID')->sortable(),
                TextColumn::make('pasien.name')->label('Pasien')->searchable(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('dokter.name')->label('Dokter')->toggleable(),
                TextColumn::make('terapis.name')->label('Terapis')->toggleable(),
                TextColumn::make('tanggal_jam')->label('Jadwal')->dateTime()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('tanggal_jam', 'desc')
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
