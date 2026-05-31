<?php

namespace App\Providers;

use App\Filament\Widgets\DoctorLeaderboard;
use App\Models\PatientForm;
use App\Observers\PatientFormObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Filament\Widgets\FinancialStats; 
use App\Filament\Widgets\FinancialChart;
use App\Filament\Widgets\TherapistLeaderboard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS untuk ngrok / production
        if (str_contains(request()->getHost(), 'ngrok') || $this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Locale Bahasa Indonesia untuk Carbon (->translatedFormat, diffForHumans, dll)
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'ID');

        // DAFTARKAN WIDGET AGAR BISA DIPANGGIL DI BLADE
        Livewire::component('financial-stats', FinancialStats::class);
        Livewire::component('financial-chart', FinancialChart::class);

        Livewire::component('doctor-leaderboard', DoctorLeaderboard::class);
        Livewire::component('therapist-leaderboard', TherapistLeaderboard::class);

        // Observer: kirim WA ke pasien saat dokter selesai me-review pra-konsul.
        PatientForm::observe(PatientFormObserver::class);
    }
}
