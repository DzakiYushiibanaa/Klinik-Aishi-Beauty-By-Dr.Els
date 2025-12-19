<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\RekamMedis\RekamMedisResource;
use App\Models\RekamMedis;
use App\Support\CodeGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buatRekamMedis')
            ->label('Buat Rekam Medis')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->visible(fn () =>
                ($this->record->status === 'selesai')
                && !$this->record->rekamMedis
            )
            ->action(function () {
                $appointment = $this->record->loadMissing(['rekamMedis']);

                // double safety
                if ($appointment->rekamMedis) {
                    Notification::make()->title('Rekam medis sudah ada')->warning()->send();
                    return;
                }

                $rm = DB::transaction(function () use ($appointment) {
                    $noKunjungan = CodeGenerator::prefixNumber(
                        new RekamMedis(),
                        'no_kunjungan',
                        'KJ',
                        6,
                        '-'
                    );

                    return RekamMedis::create([
                        'no_kunjungan'   => $noKunjungan,
                        'appointment_id' => $appointment->appointment_id,
                        'pasien_id'      => $appointment->user_id,
                        'dokter_id'      => $appointment->dokter_id,
                        'terapis_id'     => $appointment->terapis_id,
                        'keluhan'        => $appointment->catatan_pasien, // opsional tapi enak
                    ]);
                });

                Notification::make()
                    ->title('Rekam medis berhasil dibuat')
                    ->success()
                    ->send();

                $this->redirect(
                    RekamMedisResource::getUrl('edit', ['record' => $rm])
                );
            }),
            DeleteAction::make(),
        ];
    }

}
