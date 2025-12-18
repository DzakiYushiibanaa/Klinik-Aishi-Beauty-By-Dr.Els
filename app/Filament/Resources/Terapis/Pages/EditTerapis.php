<?php

namespace App\Filament\Resources\Terapis\Pages;

use App\Filament\Resources\Terapis\TerapisResource;
use App\Models\Terapis;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTerapis extends EditRecord
{
    protected static string $resource = TerapisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Terapis $terapis */
        $terapis = $this->record->loadMissing('user');

        $data['user_name']  = $terapis->user->name;
        $data['user_email'] = $terapis->user->email;
        $data['user_phone'] = $terapis->user->phone;

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Terapis
    {
        /** @var Terapis $record */
        $record->loadMissing('user');

        $record->user->update([
            'name'  => $data['user_name'],
            'email' => $data['user_email'],
            'phone' => $data['user_phone'] ?? null,
        ]);

        if (!empty($data['user_password'])) {
            $record->user->update([
                'password' => Hash::make($data['user_password']),
            ]);
        }

        $record->update([
            'jabatan'          => $data['jabatan'],
            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
            'status_kerja'     => $data['status_kerja'] ?? 'aktif',
            'foto_profil_path' => $data['foto_profil_path'] ?? null,
        ]);

        return $record;
    }
}
