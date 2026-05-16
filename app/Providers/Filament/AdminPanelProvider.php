<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
            ->brandName('Aishi Beauty')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors([
                'primary' => \Filament\Support\Colors\Color::hex('#B8956F'),
                'danger' => \Filament\Support\Colors\Color::Rose,
                'gray' => \Filament\Support\Colors\Color::Gray,
                'info' => \Filament\Support\Colors\Color::Blue,
                'success' => \Filament\Support\Colors\Color::Emerald,
                'warning' => \Filament\Support\Colors\Color::Orange,
                'purple' => \Filament\Support\Colors\Color::Purple,
            ])
            ->font('Plus Jakarta Sans')
            ->sidebarCollapsibleOnDesktop()
            ->spa(hasPrefetching: true)
            ->navigationGroups([
                NavigationGroup::make('Layanan Klinik')
                    ->label('Layanan Klinik')
                    ->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Transaksi')
                    ->label('Transaksi')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Pra Konsultasi')
                    ->label('Pra Konsultasi')
                    ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make('Katalog')
                    ->label('Katalog')
                    ->icon('heroicon-o-squares-2x2'),
                NavigationGroup::make('Forum')
                    ->label('Forum')
                    ->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('Master Data')
                    ->label('Master Data')
                    ->icon('heroicon-o-server-stack')
                    ->collapsed(),
                NavigationGroup::make('Filament Shield')
                    ->label('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="aishi-sidebar-footer" style="background-color:#2C2622; border-top:1px solid rgba(255,255,255,0.08); padding:0.5rem 0.75rem; flex-shrink:0;">
                        {{-- Profile Link --}}
                        <a 
                            href="{{ \App\Filament\Pages\EditProfile::getUrl() }}"
                            x-data="{ tooltip: false }"
                            x-effect="tooltip = $store.sidebar.isOpen ? false : { content: \'Profil Saya\', placement: document.dir === \'rtl\' ? \'left\' : \'right\', theme: $store.theme }"
                            x-tooltip.html="tooltip"
                            style="display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0.75rem; border-radius:0.625rem; color:#C8C0B8; font-size:0.875rem; font-weight:500; text-decoration:none; transition:all 200ms ease; cursor:pointer;"
                            onmouseover="this.style.backgroundColor=\'rgba(255,255,255,0.06)\'; this.style.color=\'#FAF9F6\';"
                            onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'#C8C0B8\';"
                        >
                            <svg style="width:20px; height:20px; flex-shrink:0; color:#8A8078;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span x-show="$store.sidebar.isOpen" x-transition:enter="fi-transition-enter" x-transition:enter-start="fi-transition-enter-start" x-transition:enter-end="fi-transition-enter-end">Profil Saya</span>
                        </a>

                        {{-- Log Out --}}
                        <form method="post" action="{{ route(\'filament.admin.auth.logout\') }}">
                            @csrf
                            <button 
                                type="submit" 
                                x-data="{ tooltip: false }"
                                x-effect="tooltip = $store.sidebar.isOpen ? false : { content: \'Log Out\', placement: document.dir === \'rtl\' ? \'left\' : \'right\', theme: $store.theme }"
                                x-tooltip.html="tooltip"
                                style="display:flex; align-items:center; gap:0.75rem; width:100%; padding:0.5rem 0.75rem; border-radius:0.625rem; color:#C8C0B8; font-size:0.875rem; font-weight:500; background:none; border:none; text-decoration:none; transition:all 200ms ease; cursor:pointer; font-family:inherit;"
                                onmouseover="this.style.backgroundColor=\'rgba(255,255,255,0.06)\'; this.style.color=\'#FAF9F6\';"
                                onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'#C8C0B8\';"
                            >
                                <svg style="width:20px; height:20px; flex-shrink:0; color:#8A8078;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                <span x-show="$store.sidebar.isOpen" x-transition:enter="fi-transition-enter" x-transition:enter-start="fi-transition-enter-start" x-transition:enter-end="fi-transition-enter-end">Log Out</span>
                            </button>
                        </form>
                    </div>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

                    <style>
                        /* ===================================================
                         *  AISHI BEAUTY — Admin Panel Theme
                         *  Design: Medical-Luxury (Charcoal Brown + Warm Gold)
                         *  Font: Plus Jakarta Sans (global) + Playfair Display (brand logo only)
                         *  Active Color: #B8956F
                         * =================================================== */

                        /* ---------- SIDEBAR ---------- */
                        .fi-sidebar {
                            background-color: #2C2622 !important;
                            border-right: none !important;
                            display: flex !important;
                            flex-direction: column !important;
                        }
                        .dark .fi-sidebar {
                            background-color: #2C2622 !important;
                            border-right: none !important;
                        }

                        /* Sidebar header (contains collapse btn & logo) */
                        .fi-sidebar-header {
                            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
                            padding: 1.25rem 1rem !important;
                        }

                        /* Brand / Logo in sidebar */
                        .fi-sidebar-header .fi-logo,
                        .fi-sidebar .fi-logo {
                            font-family: \'Playfair Display\', serif !important;
                            font-size: 1.35rem !important;
                            color: #FAF9F6 !important;
                            font-weight: 500 !important;
                            letter-spacing: 0.01em !important;
                        }

                        /* Sidebar nav — scrollable, takes remaining space */
                        .fi-sidebar-nav {
                            padding: 0.5rem 0.75rem !important;
                            flex: 1 1 0% !important;
                            overflow-y: auto !important;
                            min-height: 0 !important;
                        }

                        /* Sticky footer at bottom */
                        .aishi-sidebar-footer {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                        }

                        /* ---------- PAGE HEADERS & TYPOGRAPHY ---------- */
                        /* All page headings now use Plus Jakarta Sans (except brand logo which keeps Playfair) */
                        .fi-header-heading {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                            color: #2C2622 !important;
                            font-weight: 700 !important;
                            font-size: 2rem !important;
                            letter-spacing: -0.01em !important;
                        }
                        .dark .fi-header-heading {
                            color: #FAF9F6 !important;
                        }

                        /* ---------- GLOBAL FONT ENFORCEMENT ---------- */
                        /* Ensure every element in the admin panel uses Plus Jakarta Sans... */
                        .fi-body,
                        .fi-body *,
                        .fi-main,
                        .fi-main *,
                        .fi-page,
                        .fi-page *,
                        .fi-modal,
                        .fi-modal * {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                        }

                        /* ...except the brand logo "Aishi Beauty" which stays on Playfair Display */
                        .fi-sidebar-header .fi-logo,
                        .fi-sidebar-header .fi-logo *,
                        .fi-sidebar .fi-logo,
                        .fi-sidebar .fi-logo *,
                        .fi-topbar .fi-logo,
                        .fi-topbar .fi-logo *,
                        .fi-topbar-start .fi-logo,
                        .fi-topbar-start .fi-logo * {
                            font-family: \'Playfair Display\', serif !important;
                        }

                        /* ---------- TABLE STYLING ---------- */
                        /* Make search input rounded like a pill */
                        .fi-ta-search-field .fi-input-wrapper {
                            border-radius: 9999px !important;
                        }
                        .fi-ta-search-field input {
                            border-radius: 9999px !important;
                            padding-left: 1rem !important;
                        }
                        
                        /* Table Container radius */
                        .fi-ta-content {
                            border-radius: 1rem !important;
                            box-shadow: 0 4px 20px -4px rgba(0,0,0,0.05) !important;
                            border: none !important;
                        }

                        /* ---------- SECTION HEADINGS ---------- */
                        .fi-section-header-heading {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                            font-weight: 700 !important;
                            font-size: 1.25rem !important;
                            color: #2C2622 !important;
                            letter-spacing: -0.01em !important;
                        }
                        .dark .fi-section-header-heading {
                            color: #FAF9F6 !important;
                        }

                        /* ---------- MODAL / SLIDE-OVER STYLING ---------- */
                        .fi-modal-window {
                            border-radius: 1.25rem !important;
                            background-color: #FCFAF8 !important; /* Cream background for modal */
                        }
                        .fi-modal-header {
                            background-color: #FCFAF8 !important;
                            border-radius: 1.25rem 1.25rem 0 0 !important;
                        }
                        .fi-modal-header-heading {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                            font-weight: 700 !important;
                            font-size: 1.5rem !important;
                            color: #2C2622 !important;
                        }
                        .dark .fi-modal-header-heading {
                            color: #FAF9F6 !important;
                        }
                        
                        /* Sections inside modals — white card on cream background */
                        .fi-modal-content .fi-section {
                            background-color: #FFFFFF !important;
                            border: 1px solid #F0EBE1 !important;
                            border-radius: 0.75rem !important;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;
                        }
                        .dark .fi-modal-content .fi-section {
                            background-color: #241E1B !important;
                            border-color: #332C27 !important;
                        }
                        
                        /* Modal sticky footer styling */
                        .fi-modal-footer {
                            background-color: #FFFFFF !important;
                            border-top: 1px solid #F0EBE1 !important;
                            border-radius: 0 0 1.25rem 1.25rem !important;
                        }
                        .dark .fi-modal-footer {
                            background-color: #241E1B !important;
                            border-top-color: #332C27 !important;
                        }

                        /* ---------- INPUTS & TOGGLE BUTTONS ---------- */
                        .fi-input-wrapper {
                            border-radius: 0.5rem !important;
                            border-color: #E5DFD5 !important;
                            box-shadow: none !important;
                        }
                        .fi-input-wrapper:focus-within {
                            border-color: #B8956F !important;
                            box-shadow: 0 0 0 1px #B8956F !important;
                        }
                        
                        /* Grouped Toggle Buttons (Konsultasi/Treatment) */
                        /* Container box */
                        .fi-fo-toggle-buttons.fi-btn-group,
                        .fi-fo-toggle-buttons-wrp .fi-fo-toggle-buttons.fi-btn-group {
                            background-color: #FAF8F5 !important;
                            border-radius: 0.5rem !important;
                            padding: 0.25rem !important;
                            gap: 0.25rem !important;
                            border: 1px solid #EDE8E3 !important;
                            display: grid !important;
                            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                            width: 100% !important;
                            box-shadow: none !important;
                            --tw-ring-shadow: none !important;
                            --tw-shadow: none !important;
                        }
                        /* Hide radio inputs */
                        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-input {
                            position: absolute !important;
                            opacity: 0 !important;
                            pointer-events: none !important;
                            width: 0 !important;
                            height: 0 !important;
                        }
                        /* FORCE default (non-selected) styling on all fi-btn inside toggle-buttons.
                         * Use max specificity to override any fi-btn-color-* rules. */
                        .fi-fo-toggle-buttons.fi-btn-group > .fi-btn,
                        .fi-fo-toggle-buttons.fi-btn-group .fi-btn,
                        .fi-fo-toggle-buttons.fi-btn-group label.fi-btn {
                            background-color: transparent !important;
                            background: transparent !important;
                            background-image: none !important;
                            border: none !important;
                            border-color: transparent !important;
                            box-shadow: none !important;
                            --tw-shadow: none !important;
                            --tw-ring-shadow: none !important;
                            --tw-ring-color: transparent !important;
                            border-radius: 0.375rem !important;
                            color: #8A8078 !important;
                            font-weight: 500 !important;
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                            text-align: center !important;
                            justify-content: center !important;
                            padding: 0.5rem 1rem !important;
                            transition: all 0.15s ease !important;
                        }
                        .fi-fo-toggle-buttons.fi-btn-group .fi-btn:hover {
                            background-color: rgba(184, 149, 111, 0.08) !important;
                            background: rgba(184, 149, 111, 0.08) !important;
                            color: #7A5A3A !important;
                        }
                        /* SELECTED state — gold. Use both :checked+ and :has() for widest support. */
                        .fi-fo-toggle-buttons.fi-btn-group .fi-fo-toggle-buttons-input:checked + .fi-btn,
                        .fi-fo-toggle-buttons.fi-btn-group input:checked + label.fi-btn,
                        .fi-fo-toggle-buttons.fi-btn-group input:checked + .fi-btn {
                            background-color: #B8956F !important;
                            background: #B8956F !important;
                            background-image: none !important;
                            color: #FFFFFF !important;
                            box-shadow: 0 2px 6px -1px rgba(184, 149, 111, 0.35) !important;
                            font-weight: 600 !important;
                        }
                        .fi-fo-toggle-buttons.fi-btn-group .fi-fo-toggle-buttons-input:checked + .fi-btn:hover,
                        .fi-fo-toggle-buttons.fi-btn-group input:checked + .fi-btn:hover {
                            background-color: #A07E5A !important;
                            background: #A07E5A !important;
                            color: #FFFFFF !important;
                        }
                        /* Text label inside the button */
                        .fi-fo-toggle-buttons.fi-btn-group .fi-btn .fi-btn-label {
                            color: inherit !important;
                        }
                        /* Icon inside selected button stays white */
                        .fi-fo-toggle-buttons.fi-btn-group .fi-fo-toggle-buttons-input:checked + .fi-btn svg {
                            color: #FFFFFF !important;
                        }

                        /* ----- GROUP TITLES ----- */
                        .fi-sidebar-group-label,
                        .fi-sidebar-group-title {
                            color: #B8956F !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.12em !important;
                            font-size: 0.6875rem !important;
                            font-weight: 700 !important;
                            padding: 0.625rem 0.75rem 0.375rem !important;
                            margin-top: 0.5rem !important;
                        }

                        /* Group collapse/expand button icon */
                        .fi-sidebar-group-collapse-button {
                            color: #7A6F65 !important;
                        }

                        /* ----- SIDEBAR ITEMS ----- */
                        .fi-sidebar-item > a,
                        .fi-sidebar-item > button,
                        .fi-sidebar-item .fi-sidebar-item-btn {
                            border-radius: 0.625rem !important;
                            padding: 0.5rem 0.75rem !important;
                            transition: all 200ms ease !important;
                            margin-bottom: 2px !important;
                        }
                        .fi-sidebar-item-label {
                            color: #C8C0B8 !important;
                            font-weight: 500 !important;
                            font-size: 0.875rem !important;
                        }
                        .fi-sidebar-item-icon {
                            color: #8A8078 !important;
                            width: 1.25rem !important;
                            height: 1.25rem !important;
                        }

                        /* Hover */
                        .fi-sidebar-item > a:hover,
                        .fi-sidebar-item > button:hover,
                        .fi-sidebar-item .fi-sidebar-item-btn:hover {
                            background-color: rgba(255, 255, 255, 0.06) !important;
                        }
                        .fi-sidebar-item > a:hover .fi-sidebar-item-label,
                        .fi-sidebar-item > button:hover .fi-sidebar-item-label,
                        .fi-sidebar-item .fi-sidebar-item-btn:hover .fi-sidebar-item-label {
                            color: #FAF9F6 !important;
                        }
                        .fi-sidebar-item > a:hover .fi-sidebar-item-icon,
                        .fi-sidebar-item > button:hover .fi-sidebar-item-icon,
                        .fi-sidebar-item .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
                            color: #D4C4B0 !important;
                        }

                        /* Active
                         * Filament v4 menggunakan modifier .fi-active pada .fi-sidebar-item.
                         * Class lama .fi-sidebar-item-active dibiarkan untuk kompatibilitas. */
                        .fi-sidebar-item.fi-active > a,
                        .fi-sidebar-item.fi-active > button,
                        .fi-sidebar-item.fi-active .fi-sidebar-item-btn,
                        .fi-sidebar-item-active > a,
                        .fi-sidebar-item-active > button {
                            background-color: #B8956F !important;
                            background-image: none !important;
                            border-radius: 0.625rem !important;
                            box-shadow: 0 2px 6px -1px rgba(184, 149, 111, 0.35) !important;
                        }
                        .fi-sidebar-item.fi-active > a:hover,
                        .fi-sidebar-item.fi-active > button:hover,
                        .fi-sidebar-item.fi-active .fi-sidebar-item-btn:hover,
                        .fi-sidebar-item-active > a:hover,
                        .fi-sidebar-item-active > button:hover {
                            background-color: #A07E5A !important;
                        }
                        .fi-sidebar-item.fi-active .fi-sidebar-item-label,
                        .fi-sidebar-item-active > a .fi-sidebar-item-label,
                        .fi-sidebar-item-active > button .fi-sidebar-item-label {
                            color: #FFFFFF !important;
                            font-weight: 600 !important;
                        }
                        .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
                        .fi-sidebar-item-active > a .fi-sidebar-item-icon,
                        .fi-sidebar-item-active > button .fi-sidebar-item-icon {
                            color: #FFFFFF !important;
                        }

                        /* ---- SIDEBAR COLLAPSED STATE ---- */
                        .fi-sidebar-nav-collapsed-group-btn {
                            color: #8A8078 !important;
                        }
                        .fi-sidebar-nav-collapsed-group-btn:hover {
                            background-color: rgba(255, 255, 255, 0.06) !important;
                            color: #FAF9F6 !important;
                        }

                        /* ---------- TOPBAR ---------- */
                        .fi-topbar {
                            background-color: #2C2622 !important;
                            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
                        }

                        /* Topbar sidebar toggle buttons */
                        .fi-topbar-open-sidebar-btn,
                        .fi-topbar-close-sidebar-btn,
                        .fi-topbar-open-collapse-sidebar-btn,
                        .fi-topbar-close-collapse-sidebar-btn {
                            color: #C8C0B8 !important;
                        }
                        .fi-topbar-open-sidebar-btn:hover,
                        .fi-topbar-close-sidebar-btn:hover,
                        .fi-topbar-open-collapse-sidebar-btn:hover,
                        .fi-topbar-close-collapse-sidebar-btn:hover {
                            color: #FAF9F6 !important;
                            background-color: rgba(255, 255, 255, 0.06) !important;
                        }

                        /* Brand logo in topbar */
                        .fi-topbar .fi-logo,
                        .fi-topbar-start .fi-logo {
                            font-family: \'Playfair Display\', serif !important;
                            font-size: 1.25rem !important;
                            color: #FAF9F6 !important;
                            font-weight: 500 !important;
                        }

                        /* Topbar right-side icons (notifications, user menu) */
                        .fi-topbar-end .fi-icon-btn {
                            color: #C8C0B8 !important;
                        }
                        .fi-topbar-end .fi-icon-btn:hover {
                            color: #FAF9F6 !important;
                            background-color: rgba(255, 255, 255, 0.06) !important;
                        }

                        /* User menu button */
                        .fi-topbar .fi-user-menu-trigger,
                        .fi-topbar .fi-dropdown-trigger {
                            color: #C8C0B8 !important;
                        }
                        .fi-topbar .fi-user-menu-trigger:hover {
                            color: #FAF9F6 !important;
                        }

                        /* Avatar ring */
                        .fi-topbar .fi-avatar {
                            border: 2px solid rgba(184, 149, 111, 0.3) !important;
                        }

                        /* ---------- BREADCRUMBS ---------- */
                        .fi-breadcrumbs li,
                        .fi-breadcrumbs a,
                        .fi-breadcrumbs span {
                            color: #8A8078 !important;
                        }
                        .fi-breadcrumbs a:hover {
                            color: #B8956F !important;
                        }

                        /* ---------- POS TRANSAKSI BARU ---------- */
                        /* Sticky right sidebar for Ringkasan & Pembayaran */
                        @media (min-width: 768px) {
                            .fi-pos-summary-wrapper {
                                position: sticky !important;
                                top: 1.5rem !important;
                                align-self: start !important;
                                z-index: 10 !important;
                            }
                        }
                        /* Compact repeater item */
                        .fi-fo-repeater .fi-fo-repeater-item {
                            padding: 1rem !important;
                            background: #FEFEFE !important;
                            border-radius: 0.75rem !important;
                            border: 1px solid #EDE8E3 !important;
                        }
                        .fi-fo-repeater .fi-fo-repeater-actions {
                            padding-top: 0.25rem !important;
                        }
                        /* "+ Tambah Item" button styling */
                        .fi-fo-repeater-add-action .fi-btn {
                            background: transparent !important;
                            border: 1px dashed #D6C8B8 !important;
                            color: #B8956F !important;
                            width: 100% !important;
                            justify-content: center !important;
                            padding: 0.625rem 1rem !important;
                            font-weight: 500 !important;
                            box-shadow: none !important;
                        }
                        .fi-fo-repeater-add-action .fi-btn:hover {
                            background: rgba(184, 149, 111, 0.05) !important;
                            border-color: #B8956F !important;
                            color: #7A5A3A !important;
                        }

                        /* ---------- PRIMARY BUTTONS (Brand Gold #B8956F) ---------- */
                        /* Broad selector — Filament primary buttons use many class variants.
                         * This catches all: CreateAction, submit, pagination active, link actions, etc.
                         * Toggle buttons are excluded (styled separately below). */
                        .fi-btn.fi-color-primary:not(.fi-fo-toggle-buttons *),
                        .fi-btn-color-primary:not(.fi-fo-toggle-buttons *),
                        button.fi-btn-color-primary:not(.fi-fo-toggle-buttons *),
                        a.fi-btn-color-primary:not(.fi-fo-toggle-buttons *),
                        .fi-ac-btn-action.fi-color-primary,
                        .fi-ac-action.fi-color-primary {
                            background-color: #B8956F !important;
                            background: #B8956F !important;
                            border-color: #B8956F !important;
                            color: #FFFFFF !important;
                            box-shadow: 0 2px 6px -1px rgba(184, 149, 111, 0.35) !important;
                            transition: all 0.15s ease !important;
                        }
                        .fi-btn.fi-color-primary:not(.fi-fo-toggle-buttons *):hover,
                        .fi-btn-color-primary:not(.fi-fo-toggle-buttons *):hover,
                        button.fi-btn-color-primary:not(.fi-fo-toggle-buttons *):hover,
                        a.fi-btn-color-primary:not(.fi-fo-toggle-buttons *):hover {
                            background-color: #A07E5A !important;
                            background: #A07E5A !important;
                            border-color: #A07E5A !important;
                            color: #FFFFFF !important;
                            box-shadow: 0 4px 10px -1px rgba(184, 149, 111, 0.45) !important;
                        }
                        .fi-btn.fi-color-primary:not(.fi-fo-toggle-buttons *):focus,
                        .fi-btn-color-primary:not(.fi-fo-toggle-buttons *):focus {
                            box-shadow: 0 0 0 3px rgba(184, 149, 111, 0.25) !important;
                        }
                        /* White icon inside primary button */
                        .fi-btn.fi-color-primary .fi-btn-icon,
                        .fi-btn.fi-color-primary svg,
                        .fi-btn-color-primary .fi-btn-icon,
                        .fi-btn-color-primary svg,
                        [class*="fi-btn"][class*="primary"] svg {
                            color: #FFFFFF !important;
                        }

                        /* Ensure header New buttons keep pill shape & proper sizing */
                        .fi-header-actions .fi-btn.fi-color-primary,
                        .fi-header-actions .fi-btn-color-primary,
                        .fi-page-header-actions .fi-btn.fi-color-primary,
                        .fi-page-header-actions .fi-btn-color-primary {
                            padding: 0.625rem 1.25rem !important;
                            font-weight: 600 !important;
                            border-radius: 0.625rem !important;
                        }

                        /* Pagination active page */
                        .fi-pagination-item[aria-current="page"] .fi-btn,
                        .fi-pagination-item.fi-active .fi-btn {
                            background-color: #B8956F !important;
                            color: #FFFFFF !important;
                            border-color: #B8956F !important;
                        }

                        /* ---------- RESPONSIVE (mobile overlay) ---------- */
                        @media (max-width: 1023px) {
                            .fi-sidebar {
                                z-index: 40 !important;
                            }
                            .fi-sidebar-header {
                                padding: 1rem !important;
                            }
                            .fi-sidebar-nav {
                                padding: 0.5rem !important;
                            }
                        }

                        /* ---------- GLOBAL FONT ---------- */
                        .fi-sidebar,
                        .fi-sidebar *,
                        .fi-topbar,
                        .fi-topbar * {
                            font-family: \'Plus Jakarta Sans\', sans-serif !important;
                        }
                        .fi-sidebar .fi-logo,
                        .fi-topbar .fi-logo,
                        .fi-topbar-start .fi-logo {
                            font-family: \'Playfair Display\', serif !important;
                        }

                        /* ---------- SCROLL BAR ---------- */
                        .fi-sidebar-nav::-webkit-scrollbar {
                            width: 4px;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-track {
                            background: transparent;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-thumb {
                            background: rgba(255,255,255,0.1);
                            border-radius: 4px;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
                            background: rgba(255,255,255,0.2);
                        }
                    </style>
                ')
            )
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Profil Saya')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => \App\Filament\Pages\EditProfile::getUrl()),
            ])

            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
