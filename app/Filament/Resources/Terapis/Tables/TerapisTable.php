<?php

namespace App\Filament\Resources\Terapis\Tables;

use App\Models\Terapis;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class TerapisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('terapis_id', 'desc')
            ->columns([
                ImageColumn::make('foto_profil_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(48)
                    ->defaultImageUrl(fn (Terapis $record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? 'T') .
                        '&color=7F9CF5&background=EBF4FF'
                    ),

                TextColumn::make('profil')
                    ->label('PROFIL')
                    ->html()
                    ->state(function (Terapis $record) {
                        $nama = e($record->user?->name ?? '-');
                        $kode = e($record->kode_terapis ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:700; font-size:0.95rem; color:#111827;">'.$nama.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$kode.'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('kode_terapis', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->join('users', 'users.id', '=', 'terapis.user_id')
                            ->orderBy('users.name', $direction)
                            ->select('terapis.*')
                    ),

                TextColumn::make('kontak')
                    ->label('KONTAK')
                    ->html()
                    ->state(function (Terapis $record) {
                        $phone = e($record->user?->phone ?? '-');
                        $email = e($record->user?->email ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="color:#111827;">'.$phone.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$email.'</span>
                            </div>
                        ';
                    }),

                TextColumn::make('jabatan_pengalaman')
                    ->label('JABATAN & PENGALAMAN')
                    ->html()
                    ->state(function (Terapis $record) {
                        $jabatan = e($record->jabatan ?? '-');
                        $exp = ($record->pengalaman_tahun ?? 0) . ' Tahun';
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:600; color:#111827;">'.$jabatan.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$exp.'</span>
                            </div>
                        ';
                    }),

                TextColumn::make('status_kerja')
                    ->label('STATUS KERJA')
                    ->html()
                    ->state(function (Terapis $record) {
                        $isActive = strtolower($record->status_kerja ?? '') === 'aktif';
                        if ($isActive) {
                            return '<span style="display:inline-block; padding:4px 14px; border-radius:9999px; background:#F5E6D3; color:#7A5A3A; font-size:0.75rem; font-weight:600; letter-spacing:0.05em;">AKTIF</span>';
                        }
                        return '<span style="display:inline-block; padding:4px 14px; border-radius:9999px; background:#E5E7EB; color:#4B5563; font-size:0.75rem; font-weight:600; letter-spacing:0.05em;">NONAKTIF</span>';
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Detail Terapis')
                    ->modalWidth('xl')
                    ->fillForm(function (Terapis $record): array {
                        $record = Terapis::with('user')->find($record->getKey());

                        return [
                            'kode_terapis'     => $record?->kode_terapis,
                            'status_kerja'     => $record?->status_kerja,
                            'jabatan'          => $record?->jabatan,
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
                    ->modalHeading('Edit Terapis')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batalkan')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->fillForm(function (Terapis $record): array {
                        $record = Terapis::with('user')->find($record->getKey());

                        return [
                            'kode_terapis'     => $record?->kode_terapis,
                            'status_kerja'     => $record?->status_kerja,
                            'jabatan'          => $record?->jabatan,
                            'pengalaman_tahun' => $record?->pengalaman_tahun,
                            'foto_profil_path' => $record?->foto_profil_path,
                            'user_name'        => $record?->user?->name,
                            'user_email'       => $record?->user?->email,
                            'user_phone'       => $record?->user?->phone,
                        ];
                    })
                    ->using(function (Model $record, array $data): Model {
                        /** @var Terapis $record */
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
                            'kode_terapis'     => $data['kode_terapis'] ?? $record->kode_terapis,
                            'jabatan'          => $data['jabatan'] ?? $record->jabatan,
                            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
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
