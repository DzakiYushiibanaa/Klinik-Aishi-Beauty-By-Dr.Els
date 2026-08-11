<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('deskripsi')->label('Deskripsi')->searchable()->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
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
