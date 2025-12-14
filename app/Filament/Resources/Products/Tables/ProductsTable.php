<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_path')->label('Foto')->disk('public')->circular(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('deskripsi')->label('Deskripsi')->searchable()->sortable(),
                TextColumn::make('category.nama')->label('Kategori')->sortable(),
                TextColumn::make('harga')->label('Harga')->money('IDR', true)->sortable(),
                TextColumn::make('berat_gram')->label('Berat(gram)')->sortable(),
                TextColumn::make('stok')->label('Stok')->sortable(),
                TextColumn::make('tanggal_kadaluarsa')->label('Expiry Date')->sortable(),
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
