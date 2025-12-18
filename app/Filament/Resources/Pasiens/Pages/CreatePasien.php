<?php

namespace App\Filament\Resources\Pasiens\Pages;

use App\Filament\Resources\Pasiens\PasienResource;
use App\Models\Pasien;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreatePasien extends CreateRecord
{
    protected static string $resource = PasienResource::class;

    protected function handleRecordCreation(array $data): Pasien
    {
        $user = User::create([
            'name'     => $data['user_name'],
            'email'    => $data['user_email'],
            'password' => Hash::make($data['user_password']),
            'phone'    => $data['user_phone'] ?? null,
        ]);

        $user->assignRole('pasien');

        $prefix = !empty($data['is_member']) ? 'RM' : 'NM';
        $rm = CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', $prefix, 6, '-');

        return Pasien::create([
            'user_id'         => $user->id,
            'no_rekam_medis'  => $rm,
            'is_member'       => (bool) ($data['is_member'] ?? false),
            'member_since'    => ($data['is_member'] ?? false) ? ($data['member_since'] ?? now()) : null,
            'foto_profil_path'=> $data['foto_profil_path'] ?? null,
            'alamat'          => $data['alamat'] ?? null,
            'kota'            => $data['kota'] ?? null,
            'provinsi'        => $data['provinsi'] ?? null,
            'kode_pos'        => $data['kode_pos'] ?? null,
        ]);
    }
}
