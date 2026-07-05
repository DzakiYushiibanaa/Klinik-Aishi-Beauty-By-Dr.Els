<?php

namespace App\Filament\Resources\Pasiens\Tables;

use App\Models\Pasien;
use App\Support\CodeGenerator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PasiensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('pasien_id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                ImageColumn::make('foto_profil_path')
                    ->label('FOTO')
                    ->disk('public')
                    ->circular()
                    ->size(48)
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->nama ?? 'P') .
                        '&color=7F9CF5&background=EBF4FF'
                    ),

                TextColumn::make('profil_rm')
                    ->label('PROFIL & RM')
                    ->html()
                    ->state(function (Pasien $record) {
                        $nama = e($record->nama ?? '-');
                        $rm   = e($record->no_rekam_medis ?? '-');
                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:700; font-size:0.95rem; color:#111827;">'.$nama.'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.$rm.'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_rekam_medis', 'like', "%{$search}%");
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('nama', $direction)),

                TextColumn::make('identitas')
                    ->label('IDENTITAS')
                    ->html()
                    ->state(function (Pasien $record) {
                        $genderMap = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
                        $gender = $genderMap[$record->jenis_kelamin] ?? '-';

                        $tgl = $record->tanggal_lahir
                            ? Carbon::parse($record->tanggal_lahir)->format('d M Y')
                            : '-';
                        $age = $record->tanggal_lahir
                            ? Carbon::parse($record->tanggal_lahir)->age . ' thn'
                            : null;

                        $line2 = $age ? $tgl . ' (' . $age . ')' : $tgl;

                        return '
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:500; color:#111827;">'.e($gender).'</span>
                                <span style="font-size:0.8125rem; color:#8A8078; margin-top:2px;">'.e($line2).'</span>
                            </div>
                        ';
                    }),

                TextColumn::make('no_telepon')
                    ->label('KONTAK')
                    ->searchable()
                    ->placeholder('-')
                    ->color('gray'),

                TextColumn::make('is_member')
                    ->label('STATUS MEMBER')
                    ->html()
                    ->state(function (Pasien $record) {
                        if ($record->is_member) {
                            return '<span style="display:inline-block; padding:4px 12px; border-radius:9999px; background:#F5E6D3; color:#7A5A3A; font-size:0.8125rem; font-weight:500;">Member</span>';
                        }
                        return '<span style="display:inline-block; padding:4px 12px; border-radius:9999px; background:#E5E7EB; color:#4B5563; font-size:0.8125rem; font-weight:500;">Reguler</span>';
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
                    ->modalHeading('Detail Pasien')
                    ->modalWidth('xl'),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Edit Pasien')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batalkan')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->using(function (Model $record, array $data): Model {
                        /** @var Pasien $record */
                        $record->update($data);

                        $noRM = $record->no_rekam_medis;

                        // kalau sekarang jadi member, dan sebelumnya masih NM-xxxxxx, ganti ke RM-xxxxxx baru
                        if (!empty($data['is_member']) && str_starts_with($noRM, 'NM-')) {
                            $record->no_rekam_medis = CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', 'RM', 6, '-');
                            $record->save();
                        }

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
