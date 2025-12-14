<?php

namespace App\Filament\Resources\Bundles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BundlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_path')->label('Foto')->disk('public')->circular(),
                TextColumn::make('kode')->label('Kode')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('deskripsi')->label('Deskripsi'),
                TextColumn::make('harga_normal')->label('Harga Normal')->money('IDR', true)->sortable(),
                TextColumn::make('harga_promo')->label('Harga Promo')->money('IDR', true)->sortable(),
                TextColumn::make('jenis')->label('Jenis')->sortable(),
                IconColumn::make('is_member_only')->label('Khusus Member')->boolean(),
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
