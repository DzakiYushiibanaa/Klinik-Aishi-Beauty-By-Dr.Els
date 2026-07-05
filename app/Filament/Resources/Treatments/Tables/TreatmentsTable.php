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
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                TextColumn::make('layanan')
                    ->label('LAYANAN')
                    ->html()
                    ->state(function (\App\Models\Treatment $record) {
                        $img = $record->foto_path ? asset('storage/' . $record->foto_path) : 'https://ui-avatars.com/api/?name='.urlencode($record->nama).'&color=7F9CF5&background=EBF4FF';
                        $cat = $record->category ? $record->category->nama : 'Uncategorized';
                        return '
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <img src="'.$img.'" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid #fff; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);" />
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 700; font-size: 1rem; color: #111827;">'.$record->nama.'</span>
                                    <span style="font-size: 0.875rem; color: #8A8078;">'.$cat.'</span>
                                </div>
                            </div>
                        ';
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) {
                        return $query->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('nama', 'like', "%{$search}%");
                            });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) {
                        return $query->orderBy('nama', $direction);
                    }),
                
                TextColumn::make('deskripsi')
                    ->label('DESKRIPSI')
                    ->color('gray')
                    ->limit(50),
                
                TextColumn::make('harga')
                    ->label('HARGA')
                    ->money('IDR', true)
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->sortable(),
                
                TextColumn::make('operasional')
                    ->label('OPERASIONAL')
                    ->html()
                    ->state(function (\App\Models\Treatment $record) {
                        return '
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 500; color: #111827;">'.$record->durasi_menit.' Menit</span>
                            </div>
                        ';
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) {
                        return $query->orderBy('durasi_menit', $direction);
                    }),
                
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('STATUS'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Treatment')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
