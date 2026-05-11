<?php

namespace App\Filament\Resources\Terapis\Pages;

use App\Filament\Resources\Terapis\TerapisResource;
use App\Models\Terapis;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListTerapis extends ListRecords
{
    protected static string $resource = TerapisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Terapis')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create Terapis')
                ->modalWidth('xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Batalkan')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(true)
                ->using(function (array $data): Terapis {
                    $user = User::create([
                        'name'     => $data['user_name'],
                        'email'    => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'phone'    => $data['user_phone'] ?? null,
                    ]);

                    $user->assignRole('terapis');

                    $kode = $data['kode_terapis']
                        ?? CodeGenerator::monthly(new Terapis(), 'kode_terapis', 'TR');

                    return Terapis::create([
                        'user_id'          => $user->id,
                        'kode_terapis'     => $kode,
                        'jabatan'          => $data['jabatan'],
                        'pengalaman_tahun' => $data['pengalaman_tahun'] ?? null,
                        'status_kerja'     => $data['status_kerja'] ?? 'aktif',
                        'foto_profil_path' => $data['foto_profil_path'] ?? null,
                    ]);
                }),
        ];
    }
}
