<?php

namespace App\Filament\Resources\RekamMedis;

use App\Filament\Resources\RekamMedis\Pages\ListRekamMedis;
use App\Filament\Resources\RekamMedis\Schemas\RekamMedisForm;
use App\Filament\Resources\RekamMedis\Tables\RekamMedisTable;
use App\Models\RekamMedis;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RekamMedisResource extends Resource
{
    protected static ?string $model = RekamMedis::class;

    protected static string |UnitEnum| null $navigationGroup = 'Layanan Klinik';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Rekam Medis';

    public static function form(Schema $schema): Schema
    {
        return RekamMedisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RekamMedisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'appointment.items.treatment',
            'appointment.items.bundle',
            'appointment.items.consultation',
            'appointment.items.handler',
            'pasien.pasienProfile',
            'dokter',
            'terapis',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRekamMedis::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Layanan Klinik';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }
}
