<?php

namespace App\Filament\Resources\PatientForms\Pages;

use App\Filament\Resources\PatientForms\PatientFormResource;
use App\Filament\Resources\PatientForms\Schemas\PatientFormForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatientForms extends ListRecords
{
    protected static string $resource = PatientFormResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
