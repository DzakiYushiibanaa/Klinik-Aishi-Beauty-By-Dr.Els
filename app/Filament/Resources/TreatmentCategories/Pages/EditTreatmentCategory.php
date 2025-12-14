<?php

namespace App\Filament\Resources\TreatmentCategories\Pages;

use App\Filament\Resources\TreatmentCategories\TreatmentCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentCategory extends EditRecord
{
    protected static string $resource = TreatmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
