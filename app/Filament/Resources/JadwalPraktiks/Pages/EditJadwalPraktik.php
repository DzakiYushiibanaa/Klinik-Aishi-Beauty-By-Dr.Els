<?php

namespace App\Filament\Resources\JadwalPraktiks\Pages;

use App\Filament\Resources\JadwalPraktiks\JadwalPraktikResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJadwalPraktik extends EditRecord
{
    protected static string $resource = JadwalPraktikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
