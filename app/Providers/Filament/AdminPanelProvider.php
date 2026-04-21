<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->font('Poppins')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <form method="post" action="{{ route(\'filament.admin.auth.logout\') }}" class="px-4 mt-auto mb-4 block w-full text-left">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded-lg w-full text-left transition-all dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white">
                            <svg class="h-5 w-5 text-gray-400 shrink-0 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span class="flex-1">Log Out</span>
                        </button>
                    </form>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Memberikan style untuk item aktif (warna latar belakang biru dengan teks/ikon putih) */
                        .fi-sidebar-item-active > a,
                        .fi-sidebar-item-active > button {
                            background-color: rgba(var(--primary-600), 1) !important;
                            color: white !important;
                            border-radius: 0.5rem;
                        }
                        .fi-sidebar-item-active > a > svg,
                        .fi-sidebar-item-active > button > svg {
                            color: white !important;
                        }
                        
                        /* Dark mode style penyesuaian */
                        .dark .fi-sidebar-item-active > a,
                        .dark .fi-sidebar-item-active > button {
                            background-color: rgba(var(--primary-500), 1) !important;
                        }
                        
                        /* Mengatur Sidebar agar latar belakangnya benar-benar putih bersih */
                        .fi-sidebar {
                            background-color: white !important;
                            border-right: 1px solid #f1f5f9;
                        }
                        .dark .fi-sidebar {
                            background-color: #1e293b !important;
                            border-right: 1px solid #334155;
                        }
                    </style>
                ')
            )
            ->profile(\App\Filament\Pages\EditProfile::class)

            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->unsavedChangesAlerts()
            ->authMiddleware([
                Authenticate::class,
            ]);
            
    }
}
