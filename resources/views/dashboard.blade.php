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
                } elseif ($item->item_type === 'bundle' && $item->bundle) {
                    $totalHarga += $item->bundle->harga * $item->qty;
                    $itemsList[] = ['nama' => $item->bundle->nama, 'qty' => $item->qty, 'harga' => $item->bundle->harga];
                }
            }
        }
        return [
            'id' => $apt->appointment_id, 'date' => $apt->tanggal_jam->format('Y-m-d'),
            'day' => (int)$apt->tanggal_jam->format('d'), 'time' => $apt->tanggal_jam->format('H:i'),
            'title' => ucfirst($apt->jenis), 'doctor' => $apt->dokter->name ?? 'Dokter Umum',
            'status' => strtolower($apt->status), 'total_harga' => $totalHarga,
            'total_durasi' => $totalDurasi, 'items_list' => $itemsList,
        ];
    });
@endphp

<x-guest-layout title="Dashboard">

<div class="min-h-screen bg-[#FAF8F5]" x-data="{ activeTab: 'overview' }" x-on:switch-tab.window="activeTab = $event.detail">
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
                            <button @click="activeTab = '{{ $item['key'] }}'"
                                :class="activeTab === '{{ $item['key'] }}' ? 'bg-[#CFAF53]/15 text-[#1A1A1A] border-l-[4px] border-[#CFAF53] font-bold' : 'text-slate-600 hover:bg-[#FAF8F5] hover:translate-x-1 border-l-[4px] border-transparent'"
                                class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm text-left transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                                    {{ $item['label'] }}
                                </div>
                                
                                @if($item['key'] === 'prakonsul' && isset($badges['pra_konsul']) && $badges['pra_konsul'] > 0)
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badges['pra_konsul'] }}</span>
                                @elseif($item['key'] === 'orders' && isset($badges['pesanan']) && $badges['pesanan'] > 0)
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badges['pesanan'] }}</span>
                                @endif
                            </button>
                        @endforeach

                        <div class="my-2 border-t border-[#E8E0D8]/40"></div>

                        <button @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'bg-[#CFAF53]/15 text-[#1A1A1A] border-l-[4px] border-[#CFAF53] font-bold' : 'text-slate-600 hover:bg-[#FAF8F5] hover:translate-x-1 border-l-[4px] border-transparent'"
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm text-left transition-all duration-200">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profil Saya
                        </button>
                    </nav>

                    {{-- Member Card --}}
                    <div class="p-3">
                        @if($isMember)
                            <div class="p-4 rounded-xl bg-gradient-to-br from-[#CFAF53] to-[#8B6F1B] text-white relative overflow-hidden shadow-md shadow-[#CFAF53]/20">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-extrabold text-sm tracking-wide">Aishi VIP Member</h3>
                                        <span class="text-[9px] font-black bg-white/20 px-2 py-0.5 rounded tracking-widest uppercase">ACTIVE</span>
                                    </div>
                                    <p class="text-xs text-yellow-100 font-medium mb-3">Nikmati diskon 10% untuk produk & treatment.</p>
                                    <button class="text-xs font-bold bg-white/10 hover:bg-white/20 transition-colors px-3 py-1.5 rounded-lg border border-white/20 inline-flex items-center gap-1">
                                        Lihat Benefit
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('member.register') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-[#CFAF53]/10 to-transparent text-[#8B6F1B] hover:from-[#CFAF53]/20 transition-colors text-sm font-bold border border-[#CFAF53]/20">
                                <div class="bg-[#CFAF53] text-white p-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <div>
                                    Upgrade Member
                                    <p class="text-[10px] font-normal text-slate-500">Dapat Diskon & Promo</p>
                                </div>
                            </a>
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
                <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
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
                <button @click="activeTab = '{{ $nav['key'] }}'"
                    :class="activeTab === '{{ $nav['key'] }}' ? 'text-[#CFAF53]' : 'text-slate-400'"
                    class="flex flex-col items-center gap-0.5 px-1 py-1 transition-colors min-w-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $nav['icon'] !!}</svg>
                    <span class="text-[9px] font-semibold truncate">{{ $nav['label'] }}</span>
                </button>
            @endforeach
        </div>
    </nav>
</div>

{{-- Scripts --}}
@include('dashboard.scripts')

{{-- Midtrans Snap Script for Modal Payment --}}
<script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

</x-guest-layout>