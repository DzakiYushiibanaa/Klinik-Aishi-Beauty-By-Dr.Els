<?php

namespace App\Filament\Resources\JadwalPraktiks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JadwalPraktiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => 
                $query->whereDoesntHave('tenagaMedis.dokterProfile', fn($q) => 
                    $q->whereNotIn('status_praktik', ['aktif', 'Aktif'])
                )
            )
            ->columns([
                TextColumn::make('tenagaMedis.name')->label('Tenaga Medis')->searchable(),
                TextColumn::make('jenis_layanan')->label('Layanan')->sortable(),
                TextColumn::make('hari')->label('Hari')->sortable(),
                TextColumn::make('jam_mulai')
                    ->label('Mulai')
                    ->time('H:i'),
                TextColumn::make('jam_selesai')
                    ->label('Selesai')
                    ->time('H:i'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Detail Jadwal Praktik')
                    ->modalWidth('xl'),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Jadwal Praktik')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batalkan')
                    ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
