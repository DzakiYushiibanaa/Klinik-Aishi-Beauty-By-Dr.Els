<?php

namespace App\Filament\Resources\Pasiens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pasien;
use App\Support\CodeGenerator;

class PasiensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No.')->rowIndex(),
                TextColumn::make('no_rekam_medis')->label('No RM')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('tanggal_lahir')->label('Tgl Lahir')->searchable()->sortable(),
                TextColumn::make('jenis_kelamin')->label('Jenis Kelamin')->searchable()->sortable(),
                TextColumn::make('no_telepon')->label('No Telepon')->searchable(),
                IconColumn::make('is_member')->label('Member')->boolean(),
            ])->defaultSort('pasien_id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()
                    ->slideOver()
                    ->using(function (Model $record, array $data): Model {
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
