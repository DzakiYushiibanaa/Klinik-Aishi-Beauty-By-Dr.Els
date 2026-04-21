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
                TextColumn::make('jam_mulai')->label('Mulai'),
                TextColumn::make('jam_selesai')->label('Selesai'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
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
