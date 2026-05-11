<?php

namespace App\Filament\Resources\RekamMedis\Pages;

use App\Filament\Resources\RekamMedis\RekamMedisResource;
use Filament\Resources\Pages\ListRecords;

class ListRekamMedis extends ListRecords
{
    protected static string $resource = RekamMedisResource::class;

    /**
     * No header actions — rekam medis dibuat otomatis lewat flow
     * "Mulai Layanan" di halaman Appointments, bukan dari list ini.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
