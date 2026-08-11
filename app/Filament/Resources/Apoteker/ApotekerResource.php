<?php

namespace App\Filament\Resources\Apoteker;

use App\Filament\Resources\Apoteker\Pages\ListApotekers;
use App\Filament\Resources\Apoteker\Schemas\ApotekerForm;
use App\Filament\Resources\Apoteker\Tables\ApotekerTable;
use App\Models\Apoteker;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ApotekerResource extends Resource
{
    protected static ?string $model = Apoteker::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Data Apoteker';

    protected static ?string $modelLabel = 'Apoteker';

    protected static ?string $pluralLabel = 'Data Apoteker';

    protected static ?string $recordTitleAttribute = 'kode_apoteker';

    public static function form(Schema $schema): Schema
    {
        return ApotekerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApotekerTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApotekers::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-sparkles';
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
