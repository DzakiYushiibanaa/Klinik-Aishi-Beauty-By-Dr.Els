<?php

namespace App\Filament\Resources\Terapis;

use App\Filament\Resources\Terapis\Pages\CreateTerapis;
use App\Filament\Resources\Terapis\Pages\EditTerapis;
use App\Filament\Resources\Terapis\Pages\ListTerapis;
use App\Filament\Resources\Terapis\Schemas\TerapisForm;
use App\Filament\Resources\Terapis\Tables\TerapisTable;
use App\Models\Terapis;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TerapisResource extends Resource
{
    protected static ?string $model = Terapis::class;

    protected static string |UnitEnum| null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Terapis';

    protected static ?string $modelLabel = 'Terapis';

    protected static ?string $recordTitleAttribute = 'kode_terapis';

    public static function form(Schema $schema): Schema
    {
        return TerapisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TerapisTable::configure($table);
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
            'index' => ListTerapis::route('/'),
            'create' => CreateTerapis::route('/create'),
            'edit' => EditTerapis::route('/{record}/edit'),
        ];
    }
}
