<?php

namespace App\Filament\Resources\Dokters\Pages;

use App\Filament\Resources\Dokters\DokterResource;
use App\Models\Dokter;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateDokter extends CreateRecord
{
    protected static string $resource = DokterResource::class;

    protected function handleRecordCreation(array $data): Dokter
    {
        
        $user = User::create([
            'name'     => $data['user_name'],
            'email'    => $data['user_email'],
            'password' => Hash::make($data['user_password']),
            'phone'    => $data['user_phone'] ?? null,
        ]);

        $password = $data['user_password'] ?? null;

        if (!$password) {
            throw new \Exception('Password wajib diisi.');
        }

        // role dari Spatie
        $user->assignRole('dokter');

        $kode = CodeGenerator::monthly(new Dokter(), 'kode_dokter', 'DR');

        return Dokter::create([
            'user_id'          => $user->id,
            'kode_dokter'      => $kode,
            'spesialis'        => $data['spesialis'],
            'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
            'status_praktik'   => $data['status_praktik'] ?? 'aktif',
            'foto_profil_path' => $data['foto_profil_path'] ?? null,
        ]);
    }
}
