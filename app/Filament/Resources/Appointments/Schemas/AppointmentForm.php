<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Filament\Resources\Appointments\Schemas\Sections\IdentitasPasienSection;
use App\Filament\Resources\Appointments\Schemas\Sections\LayananPetugasSection;
use App\Filament\Resources\Appointments\Schemas\Sections\RincianLayananSection;
use App\Filament\Resources\Appointments\Schemas\Sections\PilihJadwalSection;
use App\Filament\Resources\Appointments\Schemas\Sections\CatatanSection;
use App\Filament\Resources\Appointments\Schemas\Sections\LanjutTreatmentSection;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IdentitasPasienSection::make(),
                LayananPetugasSection::make(),
                RincianLayananSection::make(),
                PilihJadwalSection::make(),
                CatatanSection::make(),
                LanjutTreatmentSection::make(),
            ])
            ->columns(1);
    }
}