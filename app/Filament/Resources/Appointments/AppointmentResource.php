<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Appointment;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // 1. Ambil query bawaan
        $query = parent::getEloquentQuery();
        
        // 2. Eager load relasi (biar enteng query-nya)
        $query->with(['items.treatment', 'items.bundle', 'pasien', 'dokter','terapis']);

        // 3. Ambil user login
        $user = Filament::auth()->user();

        // 4. Filter berdasarkan Role (PENTING!)
        if ($user) {
            if ($user->hasRole('pasien')) {
                // Pasien cuma liat data dia sendiri
                $query->where('user_id', $user->id);
            } elseif ($user->hasRole('dokter')) {
                // Dokter cuma liat yang dia handle
                $query->where('dokter_id', $user->id);
            } elseif ($user->hasRole('terapis')) {
                // Terapis cuma liat yang dia kerjain
                $query->where('terapis_id', $user->id);
            }
        }

        // 5. Return query yang sudah difilter
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Layanan Klinik';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
