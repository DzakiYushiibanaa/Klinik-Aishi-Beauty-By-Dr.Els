<?php

namespace App\Filament\Resources\Dokters\Tables;

use App\Models\Dokter;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class DoktersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('dokter_id', 'desc')
            ->columns([
                ImageColumn::make('foto_profil_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(48)
                    ->defaultImageUrl(fn (Dokter $record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user->name ?? 'D') .
                        '&color=7F9CF5&background=EBF4FF'
                    ),

                TextColumn::make('profil')
                    ->label('PROFIL')
                    ->html()
                    ->state(function (Dokter $record) {
                        $nama = e($record->user->name ?? '-');
                        $kode = e($record->kode_dokter ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:700; font-size:0.95rem; color:#111827;">'.$nama.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$kode.'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('kode_dokter', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->join('users', 'users.id', '=', 'dokter.user_id')
                            ->orderBy('users.name', $direction)
                            ->select('dokter.*')
                    ),

                TextColumn::make('kontak')
                    ->label('KONTAK')
                    ->html()
                    ->state(function (Dokter $record) {
                        $phone = e($record->user->phone ?? '-');
                        $email = e($record->user->email ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="color:#111827;">'.$phone.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$email.'</span>
                            </div>
                        ';
                    }),

                TextColumn::make('pengalaman_tahun')
                    ->label('PENGALAMAN')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . ' Tahun')
                    ->sortable(),

                TextColumn::make('status_praktik')
                    ->label('STATUS PRAKTIK')
                    ->html()
                    ->state(function (Dokter $record) {
                        $isActive = strtolower($record->status_praktik ?? '') === 'aktif';
                        if ($isActive) {
                            return '<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:9999px; background:#F5E6D3; color:#7A5A3A; font-size:0.8125rem; font-weight:500;">
                                <span style="width:6px; height:6px; border-radius:9999px; background:#B8956F;"></span>
                                Aktif
                            </span>';
                        }
                        return '<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:9999px; background:#FEE2E2; color:#991B1B; font-size:0.8125rem; font-weight:500;">
                            <span style="width:6px; height:6px; border-radius:9999px; background:#DC2626;"></span>
                            Nonaktif
                        </span>';
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Detail Dokter')
                    ->modalWidth('xl')
                    ->fillForm(function (Dokter $record): array {
                        $record = Dokter::with('user')->find($record->getKey());

                        return [
                            'kode_dokter'      => $record?->kode_dokter,
                            'status_praktik'   => $record?->status_praktik,
                            'pengalaman_tahun' => $record?->pengalaman_tahun,
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
                    ->modalHeading('Edit Dokter')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batalkan')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->fillForm(function (Dokter $record): array {
                        $record = Dokter::with('user')->find($record->getKey());

                        return [
                            'kode_dokter'      => $record?->kode_dokter,
                            'status_praktik'   => $record?->status_praktik,
                            'pengalaman_tahun' => $record?->pengalaman_tahun,
                            'foto_profil_path' => $record?->foto_profil_path,
                            'user_name'        => $record?->user?->name,
                            'user_email'       => $record?->user?->email,
                            'user_phone'       => $record?->user?->phone,
                        ];
                    })
                    ->using(function (Model $record, array $data): Model {
                        /** @var Dokter $record */
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
                            'kode_dokter'      => $data['kode_dokter'] ?? $record->kode_dokter,
                            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
                            'status_praktik'   => $data['status_praktik'] ?? 'aktif',
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
