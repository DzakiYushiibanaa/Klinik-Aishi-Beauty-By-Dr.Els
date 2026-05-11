<?php

namespace App\Filament\Resources\PatientForms;

use App\Filament\Resources\PatientForms\Pages\EditPatientForm;
use App\Filament\Resources\PatientForms\Pages\ListPatientForms;
use App\Filament\Resources\PatientForms\RelationManagers\RekomendasiItemsRelationManager;
use App\Filament\Resources\PatientForms\Schemas\PatientFormForm;
use App\Filament\Resources\PatientForms\Tables\PatientFormsTable;
use App\Models\PatientForm;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientFormResource extends Resource
{
    protected static ?string $model = PatientForm::class;
        public static function getNavigationGroup(): ?string
    {
        return 'Pra Konsultasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Form Pasien';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PatientFormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientFormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RekomendasiItemsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Filament::auth()->user();

        if ($user?->hasRole('pasien')) {
            $pasienId = \App\Models\Pasien::where('user_id', $user->id)->value('pasien_id');
            $query->where('pasien_id', $pasienId);
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        return $user?->hasAnyRole(['dokter', 'pasien', 'admin']) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatientForms::route('/'),
            'edit' => EditPatientForm::route('/{record}/edit'),
        ];
    }
}
