<?php

namespace App\Filament\Resources\PatientForms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientFormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_form_id')->label('ID')->sortable(),
                TextColumn::make('pasien.name')->label('Pasien')->searchable(),
                TextColumn::make('tipe_form')->label('Tipe')->badge(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('dokter.name')->label('Dokter')->toggleable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])->defaultSort('created_at', 'desc')
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
