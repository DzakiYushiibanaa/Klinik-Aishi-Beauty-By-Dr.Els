<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
                ImageColumn::make('foto_path')
                    ->label('FOTO')
                    ->disk('public')
                    ->circular()
                    ->size(56)
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->nama ?? 'P') .
                        '&color=7F9CF5&background=EBF4FF'
                    ),

                TextColumn::make('produk_kategori')
                    ->label('PRODUK & KATEGORI')
                    ->html()
                    ->state(function (\App\Models\Product $record) {
                        $cat = $record->category?->nama ?? 'Uncategorized';
                        return '
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 700; font-size: 0.95rem; color: #111827;">'.e($record->nama).'</span>
                                <span style="font-size: 0.8125rem; color: #8A8078; margin-top: 2px;">'.e($cat).'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('category', fn ($q) => $q->where('nama', 'like', "%{$search}%"));
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('nama', $direction)),

                TextColumn::make('deskripsi')
                    ->label('DESKRIPSI')
                    ->color('gray')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->deskripsi),

                TextColumn::make('harga')
                    ->label('HARGA')
                    ->money('IDR', true)
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('stok')
                    ->label('STOK')
                    ->html()
                    ->state(function (\App\Models\Product $record) {
                        $stok = (int) ($record->stok ?? 0);
                        $isLow = $stok <= 10;

                        if ($isLow) {
                            return '
                                <div style="display: inline-flex; align-items: center; gap: 6px; color: #DC2626; font-weight: 600;">
                                    <span>'.$stok.'</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            ';
                        }

                        return '<span style="color: #374151;">'.$stok.'</span>';
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('stok', $direction)),

                ToggleColumn::make('is_active')
                    ->label('STATUS'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Produk Dihapus'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Product')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Cancel')
                    ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
