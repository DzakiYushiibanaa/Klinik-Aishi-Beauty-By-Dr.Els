<?php

namespace App\Filament\Resources\Apoteker\Tables;

use App\Models\Apoteker;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ApotekerTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('apoteker_id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                ImageColumn::make('foto_profil_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(48)
                    ->defaultImageUrl(fn (Apoteker $record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? 'A') .
                        '&color=7F9CF5&background=EBF4FF'
                    ),

                TextColumn::make('profil')
                    ->label('PROFIL')
                    ->html()
                    ->state(function (Apoteker $record) {
                        $nama = e($record->user?->name ?? '-');
                        $kode = e($record->kode_apoteker ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:700; font-size:0.95rem; color:#111827;">'.$nama.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$kode.'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('kode_apoteker', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->join('users', 'users.id', '=', 'apotekers.user_id')
                            ->orderBy('users.name', $direction)
                            ->select('apotekers.*')
                    ),

                TextColumn::make('kontak')
                    ->label('KONTAK')
                    ->html()
                    ->state(function (Apoteker $record) {
                        $phone = e($record->user?->phone ?? '-');
                        $email = e($record->user?->email ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="color:#111827;">'.$phone.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$email.'</span>
                            </div>
                        ';
                    }),

                ToggleColumn::make('status_kerja')
                    ->label('STATUS KERJA')
                    ->getStateUsing(fn (Apoteker $record) => strtolower($record->status_kerja ?? '') === 'aktif')
                    ->updateStateUsing(function (Apoteker $record, bool $state) {
                        $record->update(['status_kerja' => $state ? 'aktif' : 'nonaktif']);
                        return $state;
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Detail Apoteker')
                    ->modalWidth('xl')
                    ->fillForm(function (Apoteker $record): array {
                        $record = Apoteker::with('user')->find($record->getKey());

                        return [
                            'kode_apoteker'    => $record?->kode_apoteker,
                            'status_kerja'     => $record?->status_kerja,
                            'foto_profil_path' => $record?->foto_profil_path,
                            'user_name'        => $record?->user?->name,
                            'user_email'       => $record?->user?->email,
                            'user_phone'       => $record?->user?->phone,
                        ];
                    }),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Apoteker')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batalkan')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->fillForm(function (Apoteker $record): array {
                        $record = Apoteker::with('user')->find($record->getKey());

                        return [
                            'kode_apoteker'    => $record?->kode_apoteker,
                            'status_kerja'     => $record?->status_kerja,
                            'foto_profil_path' => $record?->foto_profil_path,
                            'user_name'        => $record?->user?->name,
                            'user_email'       => $record?->user?->email,
                            'user_phone'       => $record?->user?->phone,
                        ];
                    })
                    ->using(function (Model $record, array $data): Model {
                        /** @var Apoteker $record */
                        $record->loadMissing('user');

                        $record->user->update([
                            'name'  => $data['user_name'],
                            'email' => $data['user_email'],
                            'phone' => $data['user_phone'] ?? null,
                        ]);

                        if (!empty($data['user_password'])) {
                            $record->user->update([
                                'password' => Hash::make($data['user_password']),
                            ]);
                        }

                        $record->update([
                            'kode_apoteker'    => $data['kode_apoteker'] ?? $record->kode_apoteker,
                            'status_kerja'     => $data['status_kerja'] ?? 'aktif',
                            'foto_profil_path' => $data['foto_profil_path'] ?? null,
                        ]);

                        return $record;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
