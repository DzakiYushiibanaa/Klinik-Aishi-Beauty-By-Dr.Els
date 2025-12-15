<?php

namespace App\Filament\Resources\PatientForms\Pages;

use App\Filament\Resources\PatientForms\PatientFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePatientForm extends CreateRecord
{
    protected static string $resource = PatientFormResource::class;
}
