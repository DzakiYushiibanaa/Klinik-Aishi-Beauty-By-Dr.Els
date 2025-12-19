<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
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
                TextColumn::make('items_summary')
                    ->label('Treatment')
                    ->getStateUsing(function (Appointment $record): string {
                        return $record->items->map(function ($item) {
                            $qty = (int) ($item->qty ?? 1);

                            if ($item->item_type === 'treatment' && $item->treatment) {
                                return $item->treatment->nama . ($qty > 1 ? " x{$qty}" : '');
                            }

                            if ($item->item_type === 'bundle' && $item->bundle) {
                                return 'Bundle: ' . $item->bundle->nama . ($qty > 1 ? " x{$qty}" : '');
                            }

                            return null;
                        })->filter()->implode(', ');
                    })
                    ->wrap()
                    ->limit(60),
                TextColumn::make('tanggal_jam')->label('Jadwal')->dateTime()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->toggleable(isToggledHiddenByDefault: true)->sortable(),
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
