<?php

namespace App\Filament\Resources\Pasiens\Pages;

use App\Filament\Resources\Pasiens\PasienResource;
use App\Models\Pasien;
use App\Support\CodeGenerator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditPasien extends EditRecord
{
    protected static string $resource = PasienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Pasien
    {
        /** @var Pasien $record */

        $record->update([
            'nama'             => $data['nama'] ?? null,
            'tanggal_lahir'    => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin'    => $data['jenis_kelamin'] ?? null,
            'no_telepon'       => $data['no_telepon'] ?? null,
            'is_member'        => (bool) ($data['is_member'] ?? false),
            'member_since'     => ($data['is_member'] ?? false) ? ($data['member_since'] ?? $record->member_since ?? now()) : null,
            'foto_profil_path' => $data['foto_profil_path'] ?? null,
            'alamat'           => $data['alamat'] ?? null,
            'kota'             => $data['kota'] ?? null,
            'provinsi'         => $data['provinsi'] ?? null,
            'kode_pos'         => $data['kode_pos'] ?? null,
        ]);

        $noRM = $record->no_rekam_medis;

        // kalau sekarang jadi member, dan sebelumnya masih NM-xxxxxx, ganti ke RM-xxxxxx baru
        if (!empty($data['is_member']) && str_starts_with($noRM, 'NM-')) {
            $record->no_rekam_medis = CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', 'RM', 6, '-');
            $record->save();
        }

        return $record;
    }
}
