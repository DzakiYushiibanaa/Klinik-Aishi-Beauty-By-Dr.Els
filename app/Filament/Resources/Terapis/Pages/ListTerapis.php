<?php

namespace App\Filament\Resources\Terapis\Pages;

use App\Filament\Resources\Terapis\TerapisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTerapis extends ListRecords
{
    protected static string $resource = TerapisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
