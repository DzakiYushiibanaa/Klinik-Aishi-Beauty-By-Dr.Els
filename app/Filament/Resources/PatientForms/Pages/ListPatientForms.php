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
        return [
            CreateAction::make()
                ->label('New Patient Form')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->model(\App\Models\PatientForm::class)
                ->form(fn ($form) => PatientFormForm::configure($form))
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create Patient Form')
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batalkan')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(false),
        ];
    }
}
