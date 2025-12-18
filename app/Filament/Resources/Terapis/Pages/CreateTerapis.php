<?php

namespace App\Filament\Resources\Terapis\Pages;

use App\Filament\Resources\Terapis\TerapisResource;
use App\Models\Terapis;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTerapis extends CreateRecord
{
    protected static string $resource = TerapisResource::class;

    protected function handleRecordCreation(array $data): Terapis
    {
        $user = User::create([
            'name'     => $data['user_name'],
            'email'    => $data['user_email'],
            'password' => Hash::make($data['user_password']),
            'phone'    => $data['user_phone'] ?? null,
        ]);

        $user->assignRole('terapis');

        $kode = CodeGenerator::monthly(new Terapis(), 'kode_terapis', 'TR');

        return Terapis::create([
            'user_id'          => $user->id,
            'kode_terapis'     => $kode,
            'jabatan'          => $data['jabatan'],
            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
            'status_kerja'     => $data['status_kerja'] ?? 'aktif',
            'foto_profil_path' => $data['foto_profil_path'] ?? null,
        ]);
    }
}
