<?php

namespace App\Filament\Resources\Treatments;

use App\Filament\Resources\Treatments\Pages\CreateTreatment;
use App\Filament\Resources\Treatments\Pages\EditTreatment;
use App\Filament\Resources\Treatments\Pages\ListTreatments;
use App\Filament\Resources\Treatments\Schemas\TreatmentForm;
use App\Filament\Resources\Treatments\Tables\TreatmentsTable;
use App\Models\Treatment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TreatmentResource extends Resource
{
    protected static ?string $model = Treatment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return TreatmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreatmentsTable::configure($table);
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
            'index' => ListTreatments::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-sparkles';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Layanan Klinik';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
