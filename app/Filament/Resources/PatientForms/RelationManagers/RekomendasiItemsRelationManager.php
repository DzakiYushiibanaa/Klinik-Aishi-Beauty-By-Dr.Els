<?php

namespace App\Filament\Resources\PatientForms\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RekomendasiItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'rekomendasiItems';
    protected static ?string $title = 'Item Rekomendasi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                ->label('Produk')
                ->relationship('product', 'nama')
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('treatment_id', null);
                        $set('bundle_id', null);
                    }
                }),
                Select::make('treatment_id')
                ->label('Treatment')
                ->relationship('treatment', 'nama')
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('product_id', null);
                        $set('bundle_id', null);
                    }
                }),
                Select::make('bundle_id')
                ->label('Bundle')
                ->relationship('bundle', 'nama')
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('product_id', null);
                        $set('treatment_id', null);
                    }
                }),
                Textarea::make('catatan')
                    ->label('catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('catatan')
            ->columns([
                TextColumn::make('catatan')
                    ->searchable(),
                TextColumn::make('item_tipe')
                    ->label('Tipe')
                    ->state(fn ($record) => $record->item_tipe)
                    ->badge(),
                TextColumn::make('item_nama')
                    ->label('Item')
                    ->state(fn ($record) => $record->item_nama)
                    ->searchable(),

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->headerActions([
                CreateAction::make(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('Tambah Rekomendasi'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
