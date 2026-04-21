<?php

namespace App\Filament\Resources\JadwalPraktiks;

use App\Filament\Resources\JadwalPraktiks\Pages\CreateJadwalPraktik;
use App\Filament\Resources\JadwalPraktiks\Pages\EditJadwalPraktik;
use App\Filament\Resources\JadwalPraktiks\Pages\ListJadwalPraktiks;
use App\Filament\Resources\JadwalPraktiks\Schemas\JadwalPraktikForm;
use App\Filament\Resources\JadwalPraktiks\Tables\JadwalPraktiksTable;
use App\Models\JadwalPraktik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JadwalPraktikResource extends Resource
{
    protected static ?string $model = JadwalPraktik::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return JadwalPraktikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalPraktiksTable::configure($table);
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
            'index' => ListJadwalPraktiks::route('/'),
            'create' => CreateJadwalPraktik::route('/create'),
            'edit' => EditJadwalPraktik::route('/{record}/edit'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }
}
