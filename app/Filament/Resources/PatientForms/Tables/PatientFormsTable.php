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
                TextColumn::make('pasien.nama')->label('Pasien')->searchable()->weight('medium'),
                TextColumn::make('tipe_form')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pra_konsul', 'Konsultasi Baru' => 'purple',
                        'offline_intake', 'Follow Up' => 'success',
                        'treatment', 'Treatment' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pra_konsul' => 'Konsultasi Baru',
                        'offline_intake' => 'Follow Up',
                        'treatment' => 'Treatment',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru', 'Baru' => 'warning',
                        'proses', 'Proses' => 'info',
                        'direkomendasikan', 'Direkomendasikan' => 'warning',
                        'selesai', 'Selesai' => 'success',
                        'ditinjau', 'Ditinjau' => 'info',
                        'ditutup', 'Ditutup' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                TextColumn::make('dokter.name')->label('Dokter')->toggleable(),
                TextColumn::make('created_at')->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
