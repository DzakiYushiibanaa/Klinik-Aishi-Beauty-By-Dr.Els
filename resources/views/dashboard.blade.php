@php
    $isMember = $user->pasienProfile?->is_member;
    $borderColor = $isMember ? 'border-[#CFAF53] ring-4 ring-[#CFAF53]/20' : 'border-white';
    $eventsData = $appointments->map(function($apt){
        $totalHarga = 0; $totalDurasi = 0; $itemsList = [];
        if ($apt->items) {
            foreach ($apt->items as $item) {
                if ($item->item_type === 'treatment' && $item->treatment) {
                    $totalHarga += $item->treatment->harga * $item->qty;
                    $totalDurasi += $item->treatment->durasi_menit * $item->qty;
                    $itemsList[] = ['nama' => $item->treatment->nama, 'qty' => $item->qty, 'harga' => $item->treatment->harga];
                }
            }
        }
        $handlerItem = $apt->items?->first(fn ($item) => in_array($item->item_type, ['treatment', 'consultation'], true) && $item->handler);

        return [
            'id' => $apt->appointment_id, 'date' => $apt->tanggal_jam->format('Y-m-d'),
            'day' => (int)$apt->tanggal_jam->format('d'), 'time' => $apt->tanggal_jam->format('H:i'),
            'title' => ucfirst($apt->jenis), 'doctor' => $handlerItem?->handler?->name ?? $apt->dokter?->name ?? $apt->terapis?->name ?? 'Dokter Umum',
            'status' => in_array(strtolower((string) $apt->status), ['akan_datang', 'diproses'], true)
                ? 'disetujui'
                : strtolower((string) $apt->status),
            'total_harga' => $totalHarga,
            'total_durasi' => $totalDurasi, 'items_list' => $itemsList,
        ];
    });
@endphp

<x-guest-layout title="Dashboard">

<div class="min-h-screen bg-[#FAF8F5]" 
     x-data="{ 
        activeTab: '{{ session('active_tab', 'overview') }}',
        profileIsDirty: false,
        showConfirmModal: false,
        pendingTab: '',
        highlightId: null,
        seenTabs: [],
        init() {
            let hasFlashedTab = {{ session()->has('active_tab') ? 'true' : 'false' }};
            let flashedTab = '{{ session('active_tab') }}';
            
            // Read URL query params for tab & highlight
            const urlParams = new URLSearchParams(window.location.search);
            const urlTab = urlParams.get('tab');
            const urlHighlight = urlParams.get('highlight');
            
            // If URL has params (redirected from creating new content), reset seenTabs
            if (urlTab || urlHighlight) {
                this.seenTabs = [];
                sessionStorage.removeItem('dashboardSeenTabs');
            } else {
                // Load seen tabs from sessionStorage only on normal visits
                try { this.seenTabs = JSON.parse(sessionStorage.getItem('dashboardSeenTabs') || '[]'); } catch(e) { this.seenTabs = []; }
            }
            
            if (urlTab) {
                this.activeTab = urlTab;
                sessionStorage.setItem('dashboardActiveTab', urlTab);
            } else if (hasFlashedTab && flashedTab) {
                this.activeTab = flashedTab;
                sessionStorage.setItem('dashboardActiveTab', flashedTab);
            } else {
                let savedTab = sessionStorage.getItem('dashboardActiveTab');
                if (savedTab) {
                    this.activeTab = savedTab;
                }
            }
            
            // Mark initial active tab as seen
            this.markTabSeen(this.activeTab);
            
            // Store highlight ID for card glow effect
            if (urlHighlight) {
                this.highlightId = urlHighlight;
                // Clean URL without reload
                window.history.replaceState({}, '', window.location.pathname);
                // Auto-clear highlight after 4 seconds
                setTimeout(() => { this.highlightId = null; }, 4000);
            }
            
            this.$watch('activeTab', (value) => {
                sessionStorage.setItem('dashboardActiveTab', value);
                this.markTabSeen(value);
            });

            window.addEventListener('beforeunload', (e) => {
                if (this.profileIsDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        markTabSeen(tab) {
            if (!this.seenTabs.includes(tab)) {
                this.seenTabs.push(tab);
                sessionStorage.setItem('dashboardSeenTabs', JSON.stringify(this.seenTabs));
            }
        },
        isTabSeen(tab) {
            return this.seenTabs.includes(tab);
        },
        attemptTabSwitch(tabKey) {
            if (tabKey === 'profile') tabKey = 'profile_data';

            const profileTabs = ['profile_data', 'profile_password', 'profile_alamat'];
            const isCurrentProfile = profileTabs.includes(this.activeTab) || this.activeTab === 'profile';
            const isTargetProfile = profileTabs.includes(tabKey);

            if (isCurrentProfile && this.profileIsDirty && !isTargetProfile) {
                this.pendingTab = tabKey;
                this.showConfirmModal = true;
            } else {
                this.activeTab = tabKey;
            }
        },
        confirmSwitch() {
            this.profileIsDirty = false;
            this.showConfirmModal = false;
            this.activeTab = this.pendingTab;
            
            // Revert DOM inputs to original state
            document.getElementById('profile-form')?.reset();
            document.getElementById('password-form')?.reset();
            
            // Dispatch event to reset Alpine.js state
            window.dispatchEvent(new CustomEvent('reset-alpine-profile'));
        }
     }" 
     x-on:switch-tab.window="attemptTabSwitch($event.detail)">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="flex gap-8">

            {{-- ====== SIDEBAR (Desktop) ====== --}}
            <aside class="hidden lg:flex flex-col w-[260px] shrink-0 sticky top-24 self-start">
                <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm overflow-hidden">

                    {{-- Logo --}}
                    <div class="px-6 pt-6 pb-4 border-b border-[#E8E0D8]/40">
                        <a href="{{ route('dashboard') }}" class="block">
                            <h2 class="text-2xl font-black tracking-tight text-[#1A1A1A]">Dashboard</h2>
                        </a>
                    </div>

                    {{-- Menu --}}
                    <nav class="p-3 space-y-1">
                        <p class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Menu Utama</p>

                        @php
                            $menuItems = [
                                ['key' => 'overview', 'label' => 'Overview', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
                                ['key' => 'appointment', 'label' => 'Janji Temu', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                                ['key' => 'prakonsul', 'label' => 'Pra-Konsultasi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
                                ['key' => 'orders', 'label' => 'Pesanan Saya', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>'],
                                ['key' => 'rekam_medis', 'label' => 'Rekam Medis', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                            ];
                        @endphp

                        @foreach($menuItems as $item)
                            <button @click="attemptTabSwitch('{{ $item['key'] }}')"
                                :class="activeTab === '{{ $item['key'] }}' ? 'bg-[#CFAF53]/15 text-[#1A1A1A] border-l-[4px] border-[#CFAF53] font-bold' : 'text-slate-600 hover:bg-[#FAF8F5] hover:translate-x-1 border-l-[4px] border-transparent'"
                                class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm text-left transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                                    {{ $item['label'] }}
                                </div>
                                
                                @if($item['key'] === 'appointment' && isset($badges['appointment']) && $badges['appointment'] > 0)
                                    <span x-show="!isTabSeen('appointment')" x-transition class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badges['appointment'] }}</span>
                                @endif
                                @if($item['key'] === 'prakonsul' && isset($badges['pra_konsul']) && $badges['pra_konsul'] > 0)
                                    <span x-show="!isTabSeen('prakonsul')" x-transition class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badges['pra_konsul'] }}</span>
                                @endif
                                @if($item['key'] === 'orders' && isset($badges['pesanan']) && $badges['pesanan'] > 0)
                                    <span x-show="!isTabSeen('orders')" x-transition class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badges['pesanan'] }}</span>
                                @endif
                            </button>
                        @endforeach

                        <div class="my-2 border-t border-[#E8E0D8]/40"></div>

                        <div x-data="{ openAkun: activeTab === 'profile' }">
                            <button @click="openAkun = !openAkun"
                                :class="activeTab === 'profile' ? 'bg-[#CFAF53]/15 text-[#1A1A1A] border-l-[4px] border-[#CFAF53] font-bold' : 'text-slate-600 hover:bg-[#FAF8F5] hover:translate-x-1 border-l-[4px] border-transparent'"
                                class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm text-left transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Akun Saya
                                </div>
                                <svg :class="{'rotate-180': openAkun}" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            
                            <div x-show="openAkun" x-collapse class="pl-11 pr-4 py-1 space-y-1">
                                <button @click="attemptTabSwitch('profile_data')"
                                    :class="activeTab === 'profile_data' || activeTab === 'profile' ? 'text-[#CFAF53] font-bold' : 'text-slate-500 hover:text-[#CFAF53] font-medium'"
                                    class="w-full text-left text-[13px] py-1.5 transition-colors">
                                    Profil
                                </button>
                                <button @click="attemptTabSwitch('profile_password')"
                                    :class="activeTab === 'profile_password' ? 'text-[#CFAF53] font-bold' : 'text-slate-500 hover:text-[#CFAF53] font-medium'"
                                    class="w-full text-left text-[13px] py-1.5 transition-colors">
                                    Ubah Password
                                </button>
                                <button @click="attemptTabSwitch('profile_alamat')"
                                    :class="activeTab === 'profile_alamat' ? 'text-[#CFAF53] font-bold' : 'text-slate-500 hover:text-[#CFAF53] font-medium'"
                                    class="w-full text-left text-[13px] py-1.5 transition-colors">
                                    Alamat
                                </button>
                            </div>
                        </div>
                    </nav>

                    {{-- Member Card --}}
                    <div class="p-3">
                        @if($isMember)
                            <div class="relative overflow-hidden rounded-xl border border-[#CFAF53]/35 bg-[#1A1A1A] p-4 text-white shadow-md shadow-[#1A1A1A]/10">
                                <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-[#CFAF53]/25 to-transparent"></div>
                                <div class="relative">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-[#EBD68E]">Member Aktif</p>
                                            <h3 class="mt-1 text-sm font-black tracking-tight">Aishi Beauty</h3>
                                        </div>
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#CFAF53] text-[#1A1A1A]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                    <div class="mt-3 border-t border-white/10 pt-3 text-[11px] text-slate-200">
                                        <p class="font-semibold">{{ $user->pasienProfile?->no_rekam_medis ?? 'Member Aishi' }}</p>
                                        <p class="mt-0.5 text-slate-400">
                                            Sejak {{ optional($user->pasienProfile?->member_since)->translatedFormat('d M Y') ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- User Footer --}}
                    <div class="px-4 py-3 border-t border-[#E8E0D8]/40 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-100 border-2 {{ $borderColor }} flex items-center justify-center text-sm font-bold text-[#CFAF53] overflow-hidden shrink-0">
                            @if($user->pasienProfile && $user->pasienProfile->foto_profil_path)
                                <img src="{{ asset('storage/' . $user->pasienProfile->foto_profil_path) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-[#1A1A1A] truncate">{{ $user->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors" title="Keluar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- ====== CONTENT AREA ====== --}}
            <main class="flex-1 min-w-0 pb-24 lg:pb-0">
                <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @include('dashboard.overview')
                </div>
                <div x-show="activeTab === 'appointment'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    @include('dashboard.appointments')
                </div>
                <div x-show="activeTab === 'prakonsul'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    @include('dashboard.pra-konsultasi')
                </div>
                <div x-show="activeTab === 'orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    @include('dashboard.orders')
                </div>
                <div x-show="activeTab === 'rekam_medis'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    @include('dashboard.rekam-medis')
                </div>
                <div x-show="['profile', 'profile_data', 'profile_password', 'profile_alamat'].includes(activeTab)" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    @include('dashboard.profile')
                </div>
            </main>

        </div>
    </div>

    {{-- ====== MOBILE BOTTOM NAV ====== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-[#E8E0D8]/60 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] z-50">
        <div class="flex items-center justify-around px-2 py-2">
            @php
                $mobileNav = [
                    ['key' => 'overview', 'label' => 'Overview', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
                    ['key' => 'appointment', 'label' => 'Janji', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                    ['key' => 'prakonsul', 'label' => 'Pra-Konsul', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
                    ['key' => 'orders', 'label' => 'Pesanan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>'],
                    ['key' => 'rekam_medis', 'label' => 'Rekam', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                    ['key' => 'profile', 'label' => 'Profil', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
                ];
            @endphp
            @foreach($mobileNav as $nav)
                <button @click="attemptTabSwitch('{{ $nav['key'] }}')"
                    :class="activeTab === '{{ $nav['key'] }}' ? 'text-[#CFAF53]' : 'text-slate-400'"
                    class="relative flex flex-col items-center gap-0.5 px-1 py-1 transition-colors min-w-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $nav['icon'] !!}</svg>
                    <span class="text-[9px] font-semibold truncate">{{ $nav['label'] }}</span>
                    @if($nav['key'] === 'appointment' && isset($badges['appointment']) && $badges['appointment'] > 0)
                        <span x-show="!isTabSeen('appointment')" x-transition class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $badges['appointment'] }}</span>
                    @endif
                    @if($nav['key'] === 'prakonsul' && isset($badges['pra_konsul']) && $badges['pra_konsul'] > 0)
                        <span x-show="!isTabSeen('prakonsul')" x-transition class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $badges['pra_konsul'] }}</span>
                    @endif
                    @if($nav['key'] === 'orders' && isset($badges['pesanan']) && $badges['pesanan'] > 0)
                        <span x-show="!isTabSeen('orders')" x-transition class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $badges['pesanan'] }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </nav>

    {{-- ====== MODAL KONFIRMASI UNSAVED CHANGES ====== --}}
    <div x-show="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm p-4" style="display: none;">
        <div x-show="showConfirmModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.away="showConfirmModal = false" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-[#E8E0D8]">
            <div class="p-6 sm:p-8">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-[#1A1A1A] mb-2">Ada Perubahan yang Belum Disimpan</h3>
                <p class="text-slate-500 text-sm mb-8">Anda telah mengubah data profil atau alamat namun belum menyimpannya. Apakah Anda yakin ingin pindah tab? Data yang belum disimpan akan hilang.</p>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showConfirmModal = false" class="px-5 py-2.5 rounded-full text-sm font-bold text-slate-500 hover:bg-slate-100 transition">Batal Pindah</button>
                    <button @click="confirmSwitch()" class="px-5 py-2.5 rounded-full text-sm font-bold bg-[#1A1A1A] text-white hover:bg-black shadow-lg shadow-black/20 transition">Ya, Buang Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
@include('dashboard.scripts')

{{-- Modal: profil belum lengkap (auto-show kalau session flag aktif) --}}
<x-profile-required-modal />

{{-- Midtrans Snap Script for Modal Payment --}}
<script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

</x-guest-layout>
