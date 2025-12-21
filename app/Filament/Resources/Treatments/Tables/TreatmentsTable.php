<?php

namespace App\Filament\Resources\Treatments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreatmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_path')->label('Foto')->disk('public')->circular(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('deskripsi')->label('Deskripsi'),
                TextColumn::make('harga')->label('Harga')->money('IDR', true)->sortable(),
                TextColumn::make('category.nama')->label('Kategori')->sortable(),
                TextColumn::make('durasi_menit')->label('Durasi')->suffix(' menit')->sortable(),
                TextColumn::make('min_level')->label('Minimal Level Terapis')->sortable(),
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
