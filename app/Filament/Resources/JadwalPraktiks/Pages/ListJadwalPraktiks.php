<?php

namespace App\Filament\Resources\JadwalPraktiks\Pages;

use App\Filament\Resources\JadwalPraktiks\JadwalPraktikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJadwalPraktiks extends ListRecords
{
    protected static string $resource = JadwalPraktikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
