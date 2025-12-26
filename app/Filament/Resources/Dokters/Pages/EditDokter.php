<?php

namespace App\Filament\Resources\Dokters\Pages;

use App\Filament\Resources\Dokters\DokterResource;
use App\Models\Dokter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditDokter extends EditRecord
{
    protected static string $resource = DokterResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Dokter $dokter */
        $dokter = $this->record->loadMissing('user');

        $data['user_name']  = $dokter->user->name;
        $data['user_email'] = $dokter->user->email;
        $data['user_phone'] = $dokter->user->phone;

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Dokter
    {
        /** @var Dokter $record */
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
            'kode_dokter'      => $data['kode_dokter'],
            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
            'status_praktik'   => $data['status_praktik'] ?? 'aktif',
            'foto_profil_path' => $data['foto_profil_path'] ?? null,
        ]);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
