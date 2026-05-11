<?php

namespace App\Filament\Resources\Pasiens;

use App\Filament\Resources\Pasiens\Pages\ListPasiens;
use App\Filament\Resources\Pasiens\Schemas\PasienForm;
use App\Filament\Resources\Pasiens\Tables\PasiensTable;
use App\Models\Pasien;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PasienResource extends Resource
{
    protected static ?string $model = Pasien::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Data Pasien';

    protected static ?string $modelLabel = 'Pasien';

    protected static ?string $pluralLabel = 'Data Pasien';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return PasienForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PasiensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPasiens::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }
}
