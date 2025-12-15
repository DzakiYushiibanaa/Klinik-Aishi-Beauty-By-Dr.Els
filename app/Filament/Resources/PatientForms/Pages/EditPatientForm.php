<?php

namespace App\Filament\Resources\PatientForms\Pages;

use App\Filament\Resources\PatientForms\PatientFormResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatientForm extends EditRecord
{
    protected static string $resource = PatientFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
