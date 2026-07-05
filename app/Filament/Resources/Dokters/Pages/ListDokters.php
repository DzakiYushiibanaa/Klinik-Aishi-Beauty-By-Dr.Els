<?php

namespace App\Filament\Resources\Dokters\Pages;

use App\Filament\Resources\Dokters\DokterResource;
use App\Models\Dokter;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListDokters extends ListRecords
{
    protected static string $resource = DokterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Dokter')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create / Edit Dokter')
                ->modalWidth('xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Cancel')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(true)
                ->using(function (array $data): Dokter {
                    $user = User::create([
                        'name'     => $data['user_name'],
                        'email'    => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'phone'    => $data['user_phone'] ?? null,
                    ]);

                    $user->assignRole('dokter');

                    $kode = $data['kode_dokter']
                        ?? CodeGenerator::monthly(new Dokter(), 'kode_dokter', 'DR');

                    return Dokter::create([
                        'user_id'          => $user->id,
                        'kode_dokter'      => $kode,

                        'status_praktik'   => $data['status_praktik'] ?? 'aktif',
                        'foto_profil_path' => $data['foto_profil_path'] ?? null,
                    ]);
                }),
        ];
    }
}
