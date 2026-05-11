<?php

namespace App\Filament\Resources\Dokters;

use App\Filament\Resources\Dokters\Pages\ListDokters;
use App\Filament\Resources\Dokters\Schemas\DokterForm;
use App\Filament\Resources\Dokters\Tables\DoktersTable;
use App\Models\Dokter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DokterResource extends Resource
{
    protected static ?string $model = Dokter::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Data Dokter';

    protected static ?string $modelLabel = 'Dokter';

    protected static ?string $pluralLabel = 'Data Dokter';

    protected static ?string $recordTitleAttribute = 'kode_dokter';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function form(Schema $schema): Schema
    {
        return DokterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoktersTable::configure($table);
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
            'index' => ListDokters::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-circle';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
