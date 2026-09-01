<?php

namespace App\Filament\Resources\Apoteker\Pages;

use App\Filament\Resources\Apoteker\ApotekerResource;
use App\Models\Apoteker;
use App\Models\User;
use App\Support\CodeGenerator;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListApotekers extends ListRecords
{
    protected static string $resource = ApotekerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Apoteker')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->slideOver()
                ->stickyModalFooter()
                ->modalHeading('Create Apoteker')
                ->modalWidth('xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Batalkan')
                ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
                ->createAnother(true)
                ->using(function (array $data): Apoteker {
                    $user = User::create([
                        'name'     => $data['user_name'],
                        'email'    => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'phone'    => $data['user_phone'] ?? null,
                    ]);

                    $user->assignRole('apoteker');

                    $kode = $data['kode_apoteker']
                        ?? CodeGenerator::monthly(new Apoteker(), 'kode_apoteker', 'AP');

                    return Apoteker::create([
                        'user_id'          => $user->id,
                        'kode_apoteker'    => $kode,
                        'status_kerja'     => $data['status_kerja'] ?? 'aktif',
                        'foto_profil_path' => $data['foto_profil_path'] ?? null,
                    ]);
                }),
        ];
    }
}
