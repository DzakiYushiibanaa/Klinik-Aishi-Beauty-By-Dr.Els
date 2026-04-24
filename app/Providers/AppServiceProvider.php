<?php

namespace App\Providers;

use App\Filament\Widgets\DoctorLeaderboard;
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
        // Cek apakah request datang dari 'ngrok' ATAU sedang di production
        if (str_contains(request()->getHost(), 'ngrok') || $this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // DAFTARKAN WIDGET AGAR BISA DIPANGGIL DI BLADE
        Livewire::component('financial-stats', FinancialStats::class);
        Livewire::component('financial-chart', FinancialChart::class);

        Livewire::component('doctor-leaderboard', DoctorLeaderboard::class);
        Livewire::component('therapist-leaderboard', TherapistLeaderboard::class);
    }
}
