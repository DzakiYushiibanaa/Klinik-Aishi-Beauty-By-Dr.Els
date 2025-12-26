<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use App\Notifications\AppointmentStatusChanged; // Pastikan notifikasi di-import
use Filament\Actions\Action;
use Filament\Notifications\Notification; 
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

                $id = $user->getKey(); 

                if ($user->hasRole('pasien')) {
                    $query->where('user_id', $id);
                    return;
                }

                if ($user->hasRole('dokter')) {
                    $query->where('dokter_id', $id);
                    return;
                }

                if ($user->hasRole('terapis')) {
                    $query->where('terapis_id', $id);
                    return;
                }
            })
            ->columns([
                TextColumn::make('appointment_id')->label('ID')->sortable()->searchable(),
                TextColumn::make('pasien.name')->label('Pasien')->searchable(),
                
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'konsultasi' => 'info',
                        'treatment' => 'warning',
                        default => 'gray',
                    }),

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
                
                TextColumn::make('tanggal_jam')->label('Jadwal')->dateTime('d M Y, H:i')->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diajukan' => 'gray',
                        'disetujui' => 'success',
                        'batal' => 'danger',
                        'selesai' => 'info',
                        'tidak_hadir' => 'warning',
                        default => 'gray',
                    })->sortable(),
            ])
            ->defaultSort('tanggal_jam', 'desc')
            ->filters([
                SelectFilter::make('dokter_id')
                    ->label('Filter Dokter')
                    ->relationship('dokter', 'name', fn (Builder $query) => $query->role('dokter'))
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Filament::auth()->user()?->hasRole('admin') ?? false),

                SelectFilter::make('terapis_id')
                    ->label('Filter Terapis')
                    ->relationship('terapis', 'name', fn (Builder $query) => $query->role('terapis'))
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
                    })
                    ->visible(fn () => (Filament::auth()->user()?->hasAnyRole(['dokter','terapis','pasien']) ?? false)),
            ])
            ->recordActions([
                EditAction::make(),

                // === TOMBOL SETUJUI ===
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Booking')
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'disetujui']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Disetujui'));
                        }

                        Notification::make()->title('Berhasil Disetujui')->success()->send();
                    }),

                // === TOMBOL TOLAK ===
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => $record->status === 'diajukan' && Filament::auth()->user()->hasRole('admin'))
                    ->action(function (Appointment $record) {
                        
                        $record->update(['status' => 'batal']);

                        // PERBAIKAN: Gunakan $record->pasien
                        if ($record->pasien) {
                            $record->pasien->notify(new AppointmentStatusChanged($record, 'Dibatalkan'));
                        }

                        Notification::make()->title('Booking Dibatalkan')->danger()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}