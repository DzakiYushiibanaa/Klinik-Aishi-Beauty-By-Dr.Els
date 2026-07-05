<?php

namespace App\Filament\Resources\TreatmentCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TreatmentCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn ($state): ?string => filled($state) ? $state : null)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('ditangani_oleh')
                    ->label('Ditangani Oleh')
                    ->state(function (\App\Models\TreatmentCategory $record): array {
                        $types = [];
                        if ($record->doctor_allowed) $types[] = 'Dokter';
                        if ($record->therapist_allowed) $types[] = 'Terapis';
                        return empty($types) ? ['Dokter'] : $types;
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dokter' => 'info',
                        'Terapis' => 'success',
                        default => 'gray',
                    }),

                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama')
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
