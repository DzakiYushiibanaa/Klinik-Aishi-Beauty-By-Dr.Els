<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Appointments\Support\AppointmentItemSync;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Appointment')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create Appointment')
                ->modalWidth('xl')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batalkan')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(true)
                ->mutateFormDataUsing(function (array $data) {
                    // Capture values that are not columns on the appointments table so Filament
                    // doesn't try to write them. They're persisted from ->after() below.
                    $data = AppointmentItemSync::captureAndStripConsultationId($data, 'new');
                    $data = AppointmentItemSync::captureAndStripFollowupItems($data, 'new');
                    return $data;
                })
                ->after(function ($record) {
                    AppointmentItemSync::syncConsultationItem(
                        $record,
                        AppointmentItemSync::consumeConsultationId('new'),
                    );
                    AppointmentItemSync::syncFollowupItems($record->fresh(), 'new');
                }),
        ];
    }
}
