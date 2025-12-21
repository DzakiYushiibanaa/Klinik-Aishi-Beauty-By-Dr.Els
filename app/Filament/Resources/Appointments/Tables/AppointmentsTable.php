<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
            $user = Filament::auth()->user();
            if (! $user) return;

            $id = $user->getKey(); // aman walau PK kamu user_id / id

            // pasien: hanya janji temu milik dia
            if ($user->hasRole('pasien')) {
                $query->where('user_id', $id);
                return;
            }

            // dokter: hanya janji temu dokter itu
            if ($user->hasRole('dokter')) {
                $query->where('dokter_id', $id);
                return;
            }

            // terapis: hanya janji temu terapis itu
            if ($user->hasRole('terapis')) {
                $query->where('terapis_id', $id);
                return;
            }

            // admin / apoteker / role lain: bebas (lihat semua)
        })
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
            SelectFilter::make('dokter_id')
                ->label('Filter Dokter')
                ->relationship(
                    name: 'dokter',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->role('dokter') 
                )
                ->searchable()
                ->preload()
                ->visible(fn () => Filament::auth()->user()?->hasRole('admin') ?? false),

            SelectFilter::make('terapis_id')
                ->label('Filter Terapis')
                ->relationship(
                    name: 'terapis',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->role('terapis')
                )
                ->searchable()
                ->preload()
                ->visible(fn () => Filament::auth()->user()?->hasRole('admin') ?? false),

            Filter::make('mine')
                ->label('Janji Temu Saya')
                ->query(function (Builder $query) {
                    $user = Filament::auth()->user();
                    if (! $user) return;

                    $id = $user->getKey();

                    if ($user->hasRole('dokter'))  return $query->where('dokter_id', $id);
                    if ($user->hasRole('terapis')) return $query->where('terapis_id', $id);
                    if ($user->hasRole('pasien'))  return $query->where('user_id', $id);

                    // admin dll: kalau toggle ini dinyalain, nggak ngaruh (boleh juga kamu set ke dibuat_oleh_id)
                })
                ->visible(fn () => (Filament::auth()->user()?->hasAnyRole(['dokter','terapis','pasien']) ?? false)),
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
