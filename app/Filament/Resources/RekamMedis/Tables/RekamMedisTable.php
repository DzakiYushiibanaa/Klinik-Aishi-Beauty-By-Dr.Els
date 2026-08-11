<?php

namespace App\Filament\Resources\RekamMedis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use App\Filament\Resources\RekamMedis\Tables\Concerns\HasRekamMedisColumns;
use App\Filament\Resources\RekamMedis\Tables\Concerns\HasRekamMedisFilters;
use App\Filament\Resources\RekamMedis\Tables\Concerns\HasRekamMedisActions;
use App\Filament\Resources\RekamMedis\Tables\Concerns\HasRekamMedisEditHelpers;

class RekamMedisTable
{
    use HasRekamMedisColumns;
    use HasRekamMedisFilters;
    use HasRekamMedisActions;
    use HasRekamMedisEditHelpers;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('rekam_medis_id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                self::columnTanggalKunjungan(),
                self::columnPasien(),
                self::columnDokter(),
                self::columnTerapis(),
                self::columnJenisLayanan(),
                self::columnKeluhan(),
                self::columnDiagnosa(),
                self::columnTreatment(),
                self::columnRegimen(),
                self::columnLampiran(),
            ])
            ->filters(self::filters())
            ->recordActions([
                self::actionView(),
                self::actionDownload(),
                self::actionEditRekamMedis(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
