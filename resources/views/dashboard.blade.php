@php
    // 1. Cek Status Member
    $isMember = $user->pasienProfile?->is_member;
    $borderColor = $isMember ? 'border-[#CFAF53] ring-4 ring-[#CFAF53]/20' : 'border-white';

    // 2. Persiapkan Data Kalender & List
    $eventsData = $appointments->map(function($apt){
        $totalHarga = 0;
        $totalDurasi = 0;
        $itemsList = [];

        if ($apt->items) {
            foreach ($apt->items as $item) {
                if ($item->item_type === 'treatment' && $item->treatment) {
                    $totalHarga += $item->treatment->harga * $item->qty;
                    $totalDurasi += $item->treatment->durasi_menit * $item->qty;
                    $itemsList[] = [
                        'nama' => $item->treatment->nama,
                        'qty' => $item->qty,
                        'harga' => $item->treatment->harga,
                    ];
                } elseif ($item->item_type === 'bundle' && $item->bundle) {
                    $totalHarga += $item->bundle->harga * $item->qty;
                    // Durasi bundle jika ada:
                    // $totalDurasi += $item->bundle->durasi_menit * $item->qty; 
                    $itemsList[] = [
                        'nama' => $item->bundle->nama,
                        'qty' => $item->qty,
                        'harga' => $item->bundle->harga,
                    ];
                }
            }
        }

        return [
            'id' => $apt->appointment_id,
            'date' => $apt->tanggal_jam->format('Y-m-d'),
            'day' => (int)$apt->tanggal_jam->format('d'),
            'time' => $apt->tanggal_jam->format('H:i'),
            'title' => ucfirst($apt->jenis),
            'title_full' => ucfirst($apt->jenis) . ' - ' . $apt->no_antrian,
            'doctor' => $apt->dokter->name ?? 'Dokter Umum',
            'status' => strtolower($apt->status),
            'total_harga' => $totalHarga,
            'total_durasi' => $totalDurasi,
            'items_list' => $itemsList,
        ];
    });
@endphp

<x-guest-layout title="Dashboard">
    
    {{-- BACKGROUND ACCENT --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-[#CFAF53]/10 -z-10"></div>

    <div class="mx-auto max-w-6xl px-4 py-8" x-data="{ activeTab: 'appointment' }">
        
        {{-- ====================================================================== --}}
        {{-- 1. HEADER PROFILE --}}
        {{-- ====================================================================== --}}
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 mb-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#CFAF53]/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
                
                {{-- FOTO PROFIL --}}
                <div class="shrink-0 relative group">
                    <div class="h-24 w-24 md:h-28 md:w-28 rounded-full bg-slate-50 border-4 {{ $borderColor }} shadow-lg flex items-center justify-center text-3xl font-bold text-[#CFAF53] overflow-hidden relative transition-all duration-300">
                        @if($user->pasienProfile && $user->pasienProfile->foto_profil_path)
                            <img src="{{ asset('storage/' . $user->pasienProfile->foto_profil_path) }}" class="h-full w-full object-cover transition transform group-hover:scale-105 duration-500">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                        
                        {{-- OVERLAY (Hanya untuk NON-MEMBER) --}}
                        @if(!$isMember)
                            <a href="{{ route('member.register') }}" class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 cursor-pointer text-white text-[10px] font-bold text-center leading-tight z-10">
                                Upgrade<br>Member
                            </a>
                        @endif
                    </div>
                    
                    {{-- BADGE STATUS --}}
                    @if($isMember)
                        <div class="absolute -bottom-3 -right-3 z-20">
                            <div class="bg-gradient-to-r from-yellow-300 via-[#CFAF53] to-[#B59B48] text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg border-2 border-white flex items-center gap-1">
                                <svg class="w-3 h-3 text-white drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                MEMBER
                            </div>
                        </div>
                    @else
                        <a href="{{ route('member.register') }}" class="absolute -bottom-2 -right-6 bg-white text-slate-500 hover:text-[#CFAF53] text-[10px] font-bold px-3 py-1 rounded-full shadow-md border border-slate-100 flex items-center gap-1 transition hover:scale-105 z-20">
                            <span>+ Join Member</span>
                        </a>
                    @endif
                </div>

                {{-- INFO TEKS --}}
                <div class="flex-1 text-center md:text-left space-y-2">
                    <div>
                        <p class="text-sm text-slate-400 font-medium tracking-wide uppercase mb-1">Selamat Datang,</p>
                        <h1 class="text-2xl md:text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                            {{ $user->name }}
                        </h1>
                    </div>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $user->email }}
                        </div>

                        @if($user->pasienProfile?->no_rekam_medis)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#CFAF53]/10 border border-[#CFAF53]/30 text-[#8B6F1B] text-xs font-bold tracking-wide">
                                RM: {{ $user->pasienProfile->no_rekam_medis }}
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-400 text-xs font-medium">
                                Belum ada RM
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TOMBOL ACTION --}}
                <div class="mt-2 md:mt-0">
                    <a href="{{ route('appointment.create') }}" class="group flex items-center gap-3 rounded-2xl bg-[#CFAF53] px-8 py-4 text-sm font-bold text-slate-900 shadow-xl shadow-[#CFAF53]/20 transition transform hover:bg-[#b59b48] hover:-translate-y-1 hover:shadow-2xl">
                        <span class="flex flex-col items-start leading-none">
                            <span class="text-[10px] opacity-70 font-normal mb-1">Butuh Perawatan?</span>
                            <span>Buat Janji Baru</span>
                        </span>
                        <div class="bg-black/10 rounded-full p-1 group-hover:translate-x-1 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                    </a>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- ====================================================================== --}}
            {{-- 2. SIDEBAR MENU --}}
            {{-- ====================================================================== --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                    <nav class="flex flex-col p-2 space-y-1">
                        <button @click="activeTab = 'appointment'" 
                            :class="activeTab === 'appointment' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Riwayat Janji Temu
                        </button>

                        <button @click="activeTab = 'prakonsul'" 
                            :class="activeTab === 'prakonsul' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Riwayat Pra-Konsul
                        </button>

                        <button @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Pesanan Produk
                        </button>

                        <div class="my-2 border-t border-slate-50"></div>

                        <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Profil Saya
                        </button>

                        <div class="my-2 border-t border-slate-50"></div>

                        {{-- MEMBER AREA --}}
                        @if($isMember)
                            <div class="mx-2 mb-2 p-4 rounded-xl bg-gradient-to-br from-[#CFAF53] to-[#8B6F1B] text-white shadow-lg relative overflow-hidden group">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-white/20 rounded-full blur-xl"></div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-2">
                                        <svg class="w-5 h-5 text-yellow-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="text-[10px] font-bold bg-white/20 px-2 py-0.5 rounded text-white tracking-wider">ACTIVE</span>
                                    </div>
                                    <p class="text-xs font-normal text-yellow-100">Member Sejak</p>
                                    <p class="text-sm font-bold">
                                        {{ \Carbon\Carbon::parse($user->pasienProfile->member_since ?? now())->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('member.register') }}" 
                            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-[#CFAF53]/10 to-transparent text-[#8B6F1B] hover:from-[#CFAF53]/20 transition-all text-sm text-left font-bold mb-2 border border-[#CFAF53]/20">
                                <div class="bg-[#CFAF53] text-white p-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <div>
                                    Upgrade Member
                                    <p class="text-[10px] font-normal opacity-80 text-slate-500">Dapat Diskon & Promo</p>
                                </div>
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 transition-all text-sm text-left font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar Aplikasi
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            {{-- ====================================================================== --}}
            {{-- 3. CONTENT AREA --}}
            {{-- ====================================================================== --}}
            <div class="lg:col-span-3">
                
                {{-- TAB 1: APPOINTMENTS --}}
                <div x-show="activeTab === 'appointment'" x-transition.opacity.duration.300ms x-data="{ viewMode: 'list' }">
                    
                    {{-- FILTER & SWITCHER ROW --}}
                    <div class="flex flex-col mb-6">
                        
                        {{-- Header Row (Judul & Switcher) --}}
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h2 class="text-xl font-bold text-slate-800">
                                Janji Temu Saya
                            </h2>

                            <div class="bg-slate-100 p-1 rounded-xl flex items-center">
                                <button @click="viewMode = 'list'" 
                                    :class="viewMode === 'list' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    List
                                </button>
                                <button @click="viewMode = 'calendar'" 
                                    :class="viewMode === 'calendar' ? 'bg-white text-[#CFAF53] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Kalender
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- MODE 1: LIST VIEW --}}
                    <div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-300">
                        @if($appointments->isEmpty())
                            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center shadow-sm">
                                <div class="w-20 h-20 bg-[#CFAF53]/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-10 h-10 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">Belum ada janji temu</h3>
                                <p class="text-slate-500 text-sm mt-1 max-w-xs mx-auto">Anda belum memiliki riwayat atau jadwal perawatan.</p>
                                <a href="{{ route('appointment.create') }}" class="mt-6 inline-block bg-[#CFAF53] hover:bg-[#b59b48] text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-yellow-200 transition transform hover:-translate-y-1">
                                    Buat Janji Baru
                                </a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($appointments as $apt)
                                    @php
                                        $aptStatusClass = strtolower($apt->status); // diajukan, disetujui, selesai, batal
                                        
                                        // Proses perhitungan items
                                        $totalDurasi = 0;
                                        $totalHarga = 0;
                                        $hasItems = false;
                                        if($apt->items) {
                                            foreach($apt->items as $item) {
                                                if($item->item_type == 'treatment' && $item->treatment) {
                                                    $totalDurasi += $item->treatment->durasi_menit * $item->qty;
                                                    $totalHarga += $item->treatment->harga * $item->qty;
                                                } elseif($item->item_type == 'bundle' && $item->bundle) {
                                                    $totalHarga += $item->bundle->harga * $item->qty;
                                                }
                                            }
                                            $hasItems = true;
                                        }
                                    @endphp
                                    
                                    {{-- Wrapper Card Janji Temu --}}
                                    <div x-transition
                                         x-data="{ openCancelModal: false, showRincian: false }" 
                                         class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition relative flex flex-col md:flex-row gap-6">
                                        
                                        {{-- 1. LEFT FLANK (KOTAK TANGGAL) --}}
                                        <div class="flex-shrink-0">
                                            <div class="flex flex-col items-center justify-center bg-[#CFAF53]/10 text-[#8B6F1B] w-[88px] h-[92px] rounded-2xl border border-[#CFAF53]/20">
                                                <span class="text-xs font-extrabold uppercase tracking-widest">{{ $apt->tanggal_jam->format('M') }}</span>
                                                <span class="text-3xl font-black mt-1 leading-none">{{ $apt->tanggal_jam->format('d') }}</span>
                                                <span class="text-xs font-bold mt-1 opacity-70">{{ $apt->tanggal_jam->format('Y') }}</span>
                                            </div>
                                        </div>

                                        {{-- 2. MIDDLE CONTENT (TTEATMENT INFO) --}}
                                        <div class="flex-1 min-w-0 pr-0 md:pr-6 md:border-r md:border-slate-100">
                                            
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <h3 class="font-extrabold text-slate-900 text-lg sm:text-xl">{{ ucfirst($apt->jenis) }}</h3>
                                                <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 ml-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $apt->tanggal_jam->format('H:i') }}
                                                    @if($hasItems && $totalDurasi > 0)
                                                        <span>•</span> {{ $totalDurasi }} Menit
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 mt-3">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 border-2 border-white shadow flex items-center justify-center overflow-hidden shrink-0">
                                                    @if($apt->terapis && $apt->terapis->foto_profil_path)
                                                        <img src="{{ asset('storage/' . $apt->terapis->foto_profil_path) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800">{{ $apt->terapis->name ?? 'Belum Ditentukan' }}</p>
                                                    <p class="text-xs text-slate-500">{{ $apt->terapis ? 'Terapis' : 'Menunggu Admin' }}</p>
                                                </div>
                                                
                                                @if($apt->dokter)
                                                    <div class="w-[1px] h-8 bg-slate-200 mx-2"></div>
                                                    <div class="w-10 h-10 rounded-full bg-slate-100 border-2 border-white shadow flex items-center justify-center shrink-0">
                                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-800">{{ $apt->dokter->name }}</p>
                                                        <p class="text-xs text-slate-500">Dokter</p>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($aptStatusClass == 'batal')
                                                <div class="mt-4 text-xs text-rose-600 bg-rose-50 px-3 py-2 rounded-lg inline-block border border-rose-100">
                                                    <span class="font-bold">Dibatalkan:</span> "{{ $apt->cancel_reason }}"
                                                </div>
                                            @endif

                                            {{-- RINCIAN ACCORDION (Jika ada items) --}}
                                            @if($hasItems && count($apt->items) > 0)
                                                <div class="mt-4 pt-4 border-t border-slate-50">
                                                    <button @click="showRincian = !showRincian" type="button" class="flex items-center gap-1.5 text-sm font-bold text-[#CFAF53] hover:text-[#8B6F1B] transition group">
                                                        <svg :class="showRincian ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                        <span x-text="showRincian ? 'Tutup Rincian' : 'Lihat Rincian ({{ count($apt->items) }} Item)'"></span>
                                                    </button>
                                                    
                                                    <div x-show="showRincian" x-collapse.duration.300ms class="mt-3 overflow-hidden">
                                                        <div class="space-y-2">
                                                            @foreach($apt->items as $item)
                                                                @if($item->item_type == 'treatment' && $item->treatment)
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="text-slate-600 truncate mr-4">{{ $item->treatment->nama }}</span>
                                                                        <span class="text-slate-800 font-medium whitespace-nowrap">Rp {{ number_format($item->treatment->harga, 0, ',', '.') }}</span>
                                                                    </div>
                                                                @elseif($item->item_type == 'bundle' && $item->bundle)
                                                                    <div class="flex justify-between items-center text-sm">
                                                                        <span class="text-slate-600 truncate mr-4">{{ $item->bundle->nama }}</span>
                                                                        <span class="text-slate-800 font-medium whitespace-nowrap">Rp {{ number_format($item->bundle->harga, 0, ',', '.') }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-100 font-bold">
                                                            <span class="text-slate-800 text-sm">Subtotal</span>
                                                            <span class="text-slate-900 text-sm">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- 3. RIGHT FLANK (STATUS & TOTAL & ACTION) --}}
                                        <div class="flex flex-col items-start md:items-end justify-between min-w-[150px] shrink-0 gap-4 md:gap-0">
                                            
                                            {{-- Status Badge --}}
                                            <div class="w-full md:w-auto text-left md:text-right">
                                                @php
                                                    $statusLabel = match($aptStatusClass) {
                                                        'diajukan' => 'Diajukan',
                                                        'disetujui' => 'Akan Datang',
                                                        'selesai' => 'Selesai',
                                                        'batal' => 'Dibatalkan',
                                                        default => ucfirst($apt->status)
                                                    };
                                                    
                                                    $statusBadgeClass = match($aptStatusClass) {
                                                        'diajukan' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                                        'disetujui' => 'bg-amber-50 text-amber-600 border-amber-200', // Sesuai referensi
                                                        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                                                    };
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadgeClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </div>

                                            {{-- Total Biaya Besar --}}
                                            <div class="w-full md:w-auto text-left md:text-right mt-4 md:mt-0">
                                                <p class="text-xs text-slate-400 mb-0.5">Total Biaya</p>
                                                <p class="text-xl font-bold text-slate-900">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                            </div>

                                            {{-- Tombol Batal --}}
                                            @if(in_array($aptStatusClass, ['diajukan', 'disetujui']) && $apt->tanggal_jam->isFuture())
                                                <div class="w-full md:w-auto mt-4 md:mt-0">
                                                    <button @click="openCancelModal = true" type="button" class="w-full md:w-auto text-xs font-bold text-slate-700 hover:text-rose-600 border border-slate-200 hover:border-rose-300 hover:bg-rose-50 px-4 py-2 rounded-xl transition">
                                                        Batalkan Janji
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Modal Batal (Stay same logic) --}}
                                        <div x-show="openCancelModal" style="display: none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                                            <div class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl relative" @click.away="openCancelModal = false">
                                                <h3 class="text-lg font-bold text-slate-900">Batalkan Janji?</h3>
                                                <p class="text-sm text-slate-500 mt-2">Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan.</p>
                                                <form action="{{ route('appointments.cancel', $apt->appointment_id) }}" method="POST" class="mt-4">
                                                    @csrf @method('PATCH')
                                                    <textarea name="cancel_reason" rows="2" required class="w-full rounded-xl border-slate-200 text-sm mb-4" placeholder="Alasan pembatalan..."></textarea>
                                                    <div class="flex gap-3">
                                                        <button type="button" @click="openCancelModal = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                                                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-bold shadow-lg hover:bg-rose-700">Ya, Batalkan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- MODE 2: CALENDAR VIEW --}}
                    <div x-show="viewMode === 'calendar'" x-transition:enter="transition ease-out duration-300" style="display: none;">
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" x-data="appCalendar()">
                            
                            {{-- Header Kalender --}}
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <span x-text="monthNames[month]" class="text-lg font-bold text-slate-800"></span>
                                    <span x-text="year" class="ml-1 text-lg font-normal text-slate-500"></span>
                                </div>
                                <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden">
                                    <button @click="prevMonth()" class="px-3 py-1 hover:bg-slate-50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                                    <button @click="currentMonth()" class="px-3 py-1 text-xs font-bold text-[#CFAF53]">Hari Ini</button>
                                    <button @click="nextMonth()" class="px-3 py-1 hover:bg-slate-50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                                </div>
                            </div>

                            {{-- Grid Hari --}}
                            <div class="grid grid-cols-7 mb-2">
                                <template x-for="(day, index) in dayNames" :key="index">
                                    <div class="text-center text-xs font-bold text-slate-400 uppercase" x-text="day"></div>
                                </template>
                            </div>

                            {{-- Grid Tanggal --}}
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="blank in blankdays" :key="blank"><div class="h-16 md:h-20 border border-transparent"></div></template>
                                
                                <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                                    <div @click="selectDate(date)" 
                                         class="h-16 md:h-20 border rounded-lg relative cursor-pointer flex flex-col items-center justify-start pt-2 transition"
                                         :class="{
                                            'bg-[#CFAF53]/10 border-[#CFAF53] text-[#8B6F1B]': isToday(date),
                                            'bg-white border-slate-100 hover:border-[#CFAF53]': !isToday(date) && selectedDate != date,
                                            'ring-2 ring-[#CFAF53] border-[#CFAF53] z-10': selectedDate == date
                                         }">
                                        <span class="text-sm font-bold" x-text="date"></span>
                                        <template x-if="hasEvent(date)">
                                            <div class="flex gap-1 mt-1">
                                                <template x-for="evt in getEvents(date)">
                                                    <div class="w-1.5 h-1.5 rounded-full" 
                                                         :class="{
                                                            'bg-yellow-400': evt.status === 'diajukan',
                                                            'bg-blue-400': evt.status === 'disetujui',
                                                            'bg-emerald-400': evt.status === 'selesai',
                                                            'bg-rose-400': evt.status === 'batal'
                                                         }"></div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>                            {{-- Detail Bawah Kalender --}}
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <h4 class="text-xs font-bold text-slate-400 uppercase mb-3">Jadwal Tanggal: <span x-text="selectedDate + ' ' + monthNames[month]" class="text-slate-800"></span></h4>
                                
                                <div x-show="selectedEvents.length === 0" class="text-center py-5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-sm text-slate-500 font-medium">Tidak ada jadwal pada tanggal ini.</p>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="event in selectedEvents">
                                        <div class="flex flex-col gap-3 p-4 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition">
                                            
                                            {{-- Header (Waktu, Judul, Status) --}}
                                            <div class="flex items-start justify-between gap-3 border-b border-slate-50 pb-3">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex flex-col items-center justify-center bg-[#CFAF53]/10 text-[#8B6F1B] px-3 py-2 rounded-xl text-center min-w-[60px] border border-[#CFAF53]/20">
                                                        <span class="text-sm font-bold block" x-text="event.time"></span>
                                                        <span class="text-[10px] font-bold uppercase tracking-wider block opacity-70">WIB</span>
                                                    </div>
                                                    <div>
                                                        <div class="font-extrabold text-slate-900 text-base" x-text="event.title"></div>
                                                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                            Dokter: <span x-text="event.doctor" class="text-slate-700"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider border"
                                                      :class="{
                                                          'bg-yellow-50 text-yellow-700 border-yellow-200': event.status === 'diajukan',
                                                          'bg-blue-50 text-blue-700 border-blue-200': event.status === 'disetujui',
                                                          'bg-emerald-50 text-emerald-700 border-emerald-200': event.status === 'selesai',
                                                          'bg-rose-50 text-rose-700 border-rose-200': event.status === 'batal',
                                                          'bg-slate-50 text-slate-700 border-slate-200': !['diajukan','disetujui','selesai','batal'].includes(event.status)
                                                      }" 
                                                      x-text="event.status"></span>
                                            </div>

                                            {{-- List Perawatan --}}
                                            <template x-if="event.items_list && event.items_list.length > 0">
                                                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                                    <template x-for="item in event.items_list">
                                                        <div class="flex justify-between items-start text-sm mb-1.5 last:mb-0">
                                                            <div class="flex items-start gap-2 text-slate-700">
                                                                <span class="opacity-50 mt-0.5">•</span>
                                                                <span class="font-medium" x-text="item.nama"></span>
                                                            </div>
                                                            <span class="text-slate-500 text-xs font-mono mt-0.5" x-text="'Rp ' + item.harga.toLocaleString('id-ID')"></span>
                                                        </div>
                                                    </template>
                                                    
                                                    <template x-if="event.total_harga > 0">
                                                        <div class="flex justify-between items-center mt-2.5 pt-2 border-t border-slate-200">
                                                            <span class="text-xs font-bold text-slate-500">Total Biaya</span>
                                                            <span class="text-sm font-bold text-[#CFAF53]" x-text="'Rp ' + event.total_harga.toLocaleString('id-ID')"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: PRA KONSUL --}}
                <div x-show="activeTab === 'prakonsul'" x-transition.opacity.duration.300ms style="display: none;">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">📋 Riwayat Pra-Konsul</h2>
                    @if($praKonsuls->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                            <p class="text-slate-500 text-sm">Belum ada data pra-konsultasi.</p>
                            <a href="{{ route('pra.index') }}" class="text-[#CFAF53] font-bold text-sm mt-2 inline-block">Isi Formulir Sekarang</a>
                        </div>
                    @else
                        <div class="grid gap-4">
                            @foreach($praKonsuls as $form)
                                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs text-slate-400">ID: #PK-{{ $form->patient_form_id }} • {{ $form->created_at->format('d M Y') }}</p>
                                            <p class="font-bold text-slate-900 mt-1 line-clamp-1">{{ $form->keluhan_utama ?? 'Tidak ada judul' }}</p>
                                        </div>
                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">{{ $form->status ?? 'Submitted' }}</span>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between items-center">
                                        <p class="text-xs text-slate-500">Dokter: {{ $form->dokter->name ?? 'Menunggu...' }}</p>
                                        <a href="{{ route('pra.show', $form->patient_form_id) }}" class="text-sm font-bold text-[#CFAF53] hover:text-[#8B6F1B]">Lihat Detail &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TAB 3: ORDERS --}}
                <div x-show="activeTab === 'orders'" x-transition.opacity.duration.300ms style="display: none;">
                    
                    {{-- Header Tab --}}
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            🛍️ Pesanan Produk
                        </h2>
                        <span class="bg-slate-100 text-slate-500 text-xs font-bold px-2 py-1 rounded-lg">
                            {{ $orders->count() }} Transaksi
                        </span>
                    </div>
                    
                    @if($orders->isEmpty())
                        {{-- EMPTY STATE --}}
                        <div class="flex flex-col items-center justify-center bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center shadow-sm">
                            <div class="w-20 h-20 bg-[#CFAF53]/10 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Belum ada pesanan</h3>
                            <p class="text-slate-500 text-sm mt-1 max-w-xs mx-auto">Yuk mulai rawat kulitmu dengan produk terbaik dari kami.</p>
                            <a href="{{ route('shop.index') }}" class="mt-6 bg-[#CFAF53] hover:bg-[#b59b48] text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-yellow-200 transition transform hover:-translate-y-1">
                                Mulai Belanja
                            </a>
                        </div>
                    @else
                        {{-- LIST ORDER --}}
                        <div class="space-y-6">
                            @foreach($orders as $order)
                                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 group">
                                    
                                    {{-- 1. HEADER CARD (INFO UTAMA) --}}
                                    <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                        
                                        {{-- Kiri: ID Order & Tanggal --}}
                                        <div class="flex items-center gap-4">
                                            <div class="bg-white p-2 rounded-lg border border-slate-100 shadow-sm hidden sm:block">
                                                <svg class="w-6 h-6 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-400">ORDER ID</span>
                                                    <span class="text-sm font-bold text-slate-800 font-mono tracking-wide">#{{ $order->kode_order }}</span>
                                                </div>
                                                <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
                                            </div>
                                        </div>

                                        {{-- Kanan: Badge Status Pembayaran --}}
                                        <div>
                                            @php
                                                $statusConfig = match($order->status) {
                                                    'menunggu_pembayaran' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Menunggu Pembayaran', 'icon' => '🕒'],
                                                    'sudah_dibayar', 'lunas' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Lunas', 'icon' => '✅'],
                                                    'batal' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'label' => 'Dibatalkan', 'icon' => '❌'],
                                                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'label' => ucfirst($order->status), 'icon' => '•']
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                                <span>{{ $statusConfig['icon'] }}</span>
                                                {{ $statusConfig['label'] }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- 2. BODY CONTENT (DETAIL ITEM) --}}
                                    <div class="p-6">
                                        
                                        {{-- List Item --}}
                                        <div class="space-y-4 mb-6">
                                            @foreach($order->items as $item)
                                                <div class="flex items-start justify-between group/item">
                                                    <div class="flex items-start gap-4">
                                                        {{-- Icon Placeholder Sesuai Tipe Item --}}
                                                        <div class="w-12 h-12 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 shrink-0">
                                                            @if($item->item_type == 'treatment')
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                            @elseif($item->item_type == 'membership')
                                                                <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                            @else
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1 group-hover/item:text-[#CFAF53] transition">{{ $item->description }}</h4>
                                                            <p class="text-xs text-slate-500 mt-0.5">{{ ucfirst($item->item_type) }} &bull; Qty: {{ $item->qty }}</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-bold text-slate-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- 3. INFO PENGIRIMAN & TIMELINE --}}
                                        {{-- Hanya tampil jika bukan membership & sudah dibayar --}}
                                        @if($order->order_type !== 'membership' && in_array($order->status, ['sudah_dibayar', 'lunas']))
                                            <div class="mt-6 mb-6">
                                                <div class="bg-blue-50/50 rounded-2xl p-5 border border-blue-100 relative overflow-hidden">
                                                    {{-- Background Pattern --}}
                                                    <div class="absolute right-0 top-0 w-32 h-32 bg-blue-100 rounded-full blur-3xl -mr-10 -mt-10"></div>

                                                    <div class="relative z-10">
                                                        <h5 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Status Pengiriman
                                                        </h5>

                                                        <div class="flex flex-col md:flex-row gap-6">
                                                            
                                                            {{-- TIMELINE PROGRESS (Visual Step) --}}
                                                            <div class="flex-1">
                                                                @php
                                                                    $shipStatus = $order->shipping_status;
                                                                    // Logic Status Aktif
                                                                    $isPacked = in_array($shipStatus, ['diproses', 'dikirim', 'selesai']);
                                                                    $isShipped = in_array($shipStatus, ['dikirim', 'selesai']);
                                                                    $isDone = $shipStatus == 'selesai';
                                                                @endphp
                                                                
                                                                <div class="flex items-center justify-between text-xs w-full max-w-sm relative">
                                                                    {{-- Garis Penghubung (Background) --}}
                                                                    <div class="absolute top-1.5 left-2 right-2 h-0.5 bg-slate-200 -z-10"></div>
                                                                    
                                                                    {{-- Garis Aktif (Overlay) --}}
                                                                    <div class="absolute top-1.5 left-2 h-0.5 bg-blue-500 -z-10 transition-all duration-500"
                                                                        style="width: {{ $isDone ? '95%' : ($isShipped ? '50%' : '0%') }}"></div>

                                                                    {{-- Step 1: Diproses --}}
                                                                    <div class="flex flex-col items-center gap-2 {{ $isPacked ? 'text-blue-700' : 'text-slate-400' }}">
                                                                        <div class="w-3.5 h-3.5 rounded-full {{ $isPacked ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-slate-300' }}"></div>
                                                                        <span class="font-bold bg-blue-50/80 backdrop-blur px-1 rounded">Diproses</span>
                                                                    </div>

                                                                    {{-- Step 2: Dikirim --}}
                                                                    <div class="flex flex-col items-center gap-2 {{ $isShipped ? 'text-blue-700' : 'text-slate-400' }}">
                                                                        <div class="w-3.5 h-3.5 rounded-full {{ $isShipped ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-slate-300' }}"></div>
                                                                        <span class="font-bold bg-blue-50/80 backdrop-blur px-1 rounded">Dikirim</span>
                                                                    </div>

                                                                    {{-- Step 3: Selesai --}}
                                                                    <div class="flex flex-col items-center gap-2 {{ $isDone ? 'text-emerald-700' : 'text-slate-400' }}">
                                                                        <div class="w-3.5 h-3.5 rounded-full {{ $isDone ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-slate-300' }}"></div>
                                                                        <span class="font-bold bg-blue-50/80 backdrop-blur px-1 rounded">Selesai</span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- INFO RESI --}}
                                                            <div class="md:border-l md:border-blue-200 md:pl-6 pt-4 md:pt-0 border-t border-blue-200 md:border-t-0">
                                                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Kurir & Resi</p>
                                                                <div class="flex items-center justify-between gap-4">
                                                                    <p class="text-sm font-bold text-slate-800 uppercase">{{ $order->shipping_method ?? '-' }}</p>
                                                                    
                                                                    @if($order->shipping_waybill)
                                                                        {{-- 🔥 TOMBOL COPY RESI OTOMATIS 🔥 --}}
                                                                        <div class="relative group/copy cursor-pointer" 
                                                                            onclick="navigator.clipboard.writeText('{{ $order->shipping_waybill }}'); alert('Resi disalin: {{ $order->shipping_waybill }}');">
                                                                            
                                                                            <code class="bg-white px-2 py-1 rounded border border-blue-200 text-blue-700 font-mono text-sm font-bold select-all flex items-center gap-2 hover:bg-blue-50 hover:border-blue-300 transition">
                                                                                {{ $order->shipping_waybill }}
                                                                                
                                                                                {{-- Ikon Copy --}}
                                                                                <svg class="w-3.5 h-3.5 text-blue-400 group-hover/copy:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                                </svg>
                                                                            </code>
                                                                            
                                                                            {{-- Tooltip "Salin" (Muncul saat hover) --}}
                                                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover/copy:opacity-100 transition shadow-lg pointer-events-none whitespace-nowrap">
                                                                                Klik untuk salin
                                                                            </span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-xs text-slate-400 italic">Resi belum keluar</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 4. FOOTER TOTAL & TOMBOL AKSI --}}
                                        <div class="pt-5 border-t border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                                            
                                            {{-- Total Tagihan --}}
                                            <div class="text-center sm:text-left">
                                                <p class="text-xs text-slate-400 font-medium">Total Tagihan</p>
                                                <p class="text-2xl font-extrabold text-slate-800">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                            </div>

                                            {{-- TOMBOL AKSI --}}
                                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                                
                                                {{-- KONDISI 1: BELUM BAYAR --}}
                                                @if($order->status == 'menunggu_pembayaran')
                                                    
                                                    {{-- Wrapper Actions --}}
                                                    <div class="flex gap-3 w-full sm:w-auto">
                                                        
                                                        {{-- TOMBOL BATALKAN (Dengan Modal Konfirmasi) --}}
                                                        <div x-data="{ showCancel: false }">
                                                            <button @click="showCancel = true" type="button" class="px-4 py-3 rounded-xl text-sm font-bold text-rose-500 bg-rose-50 hover:bg-rose-100 border border-rose-100 transition">
                                                                Batalkan
                                                            </button>

                                                            {{-- Modal Batal --}}
                                                            <div x-show="showCancel" 
                                                                style="display: none"
                                                                x-transition.opacity.duration.300ms
                                                                class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                                                                
                                                                <div @click.away="showCancel = false" class="bg-white w-full max-w-xs rounded-3xl p-6 shadow-2xl border border-white/20 relative overflow-hidden text-center">
                                                                    
                                                                    {{-- Ikon Sampah --}}
                                                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 mb-4">
                                                                        <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </div>

                                                                    <h3 class="text-lg font-bold text-slate-900">Batalkan Pesanan?</h3>
                                                                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                                                        Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat diurungkan.
                                                                    </p>

                                                                    <div class="mt-6 flex gap-3">
                                                                        <button @click="showCancel = false" type="button" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">
                                                                            Kembali
                                                                        </button>
                                                                        
                                                                        <form action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="flex-1">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-500 text-sm font-bold text-white shadow-lg hover:bg-rose-600 transition">
                                                                                Ya, Batal
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- TOMBOL BAYAR --}}
                                                        <a href="{{ route('payment.show', $order->kode_order) }}" class="flex-1 sm:flex-none justify-center group flex items-center gap-2 bg-[#CFAF53] hover:bg-[#b59b48] text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-yellow-100 transition transform hover:-translate-y-0.5">
                                                            <span>Bayar Sekarang</span>
                                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                        </a>
                                                    </div>

                                                {{-- KONDISI 2: SEDANG DIKIRIM (DENGAN MODAL CANTIK) --}}
                                                @elseif($order->shipping_status == 'dikirim')
                                                    
                                                    {{-- Wrapper Alpine JS untuk Modal --}}
                                                    <div x-data="{ showConfirm: false }" class="flex-1 sm:flex-none w-full sm:w-auto">
                                                        
                                                        {{-- 1. TOMBOL TRIGGER (Memicu Modal) --}}
                                                        <button @click="showConfirm = true" type="button" class="w-full justify-center flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-xl text-sm font-bold shadow-lg shadow-emerald-100 transition transform hover:-translate-y-0.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                            Pesanan Diterima
                                                        </button>

                                                        {{-- 2. MODAL KONFIRMASI --}}
                                                        <div x-show="showConfirm" 
                                                            style="display: none"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0"
                                                            x-transition:enter-end="opacity-100"
                                                            x-transition:leave="transition ease-in duration-200"
                                                            x-transition:leave-start="opacity-100"
                                                            x-transition:leave-end="opacity-0"
                                                            class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                                                            
                                                            {{-- Modal Content --}}
                                                            <div @click.away="showConfirm = false" 
                                                                x-transition:enter="transition ease-out duration-300"
                                                                x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                                                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                                x-transition:leave="transition ease-in duration-200"
                                                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                                                x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                                                                class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-white/20 relative overflow-hidden">
                                                                
                                                                {{-- Hiasan Background --}}
                                                                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

                                                                {{-- Ikon --}}
                                                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 mb-5 relative z-10">
                                                                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </div>

                                                                {{-- Teks --}}
                                                                <div class="text-center relative z-10">
                                                                    <h3 class="text-lg font-bold text-slate-900">Pesanan Sudah Diterima?</h3>
                                                                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                                                                        Pastikan barang fisik sudah sampai di tangan Anda dan kondisinya baik. Status pesanan akan diubah menjadi <span class="font-bold text-emerald-600">Selesai</span>.
                                                                    </p>
                                                                </div>

                                                                {{-- Tombol Aksi --}}
                                                                <div class="mt-6 flex gap-3 relative z-10">
                                                                    <button @click="showConfirm = false" type="button" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-800 transition">
                                                                        Belum
                                                                    </button>
                                                                    
                                                                    {{-- Form Submit Sebenarnya --}}
                                                                    <form action="{{ route('orders.complete', $order->order_id) }}" method="POST" class="flex-1">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-100 hover:bg-emerald-600 hover:shadow-emerald-200 transition transform hover:-translate-y-0.5">
                                                                            Ya, Terima
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                {{-- KONDISI 3: SELESAI --}}
                                                @elseif($order->shipping_status == 'selesai')
                                                    <button disabled class="flex-1 sm:flex-none justify-center flex items-center gap-2 bg-slate-100 text-slate-400 px-5 py-3 rounded-xl text-sm font-bold cursor-default border border-slate-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Transaksi Selesai
                                                    </button>

                                                {{-- KONDISI 4: DEFAULT (DIPROSES) --}}
                                                @else
                                                    <button disabled class="flex-1 sm:flex-none justify-center px-5 py-3 rounded-xl text-sm font-bold bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100">
                                                        Detail Pesanan
                                                    </button>
                                                @endif

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TAB 4: PROFIL SAYA --}}
                <div x-show="activeTab === 'profile'" x-transition.opacity.duration.300ms style="display: none;">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">⚙️ Edit Profil Saya</h2>

                    <form action="{{ route('pasien.profile.update') }}" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm"
                          x-data="addressSelector()">
                        @csrf
                        @method('PATCH')

                        {{-- Hidden inputs untuk API --}}
                        <input type="hidden" name="provinsi" x-model="selectedProvinceName">
                        <input type="hidden" name="kota" x-model="selectedCityName">
                        <input type="hidden" name="district_id" x-model="selectedDistrictId">
                        <input type="hidden" name="district_name" x-model="selectedDistrictName">
                        <input type="hidden" name="province_id" x-model="selectedProvinceId">
                        <input type="hidden" name="city_id" x-model="selectedCityId">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-2">Ganti Foto Profil</label>
                                <input type="file" name="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#CFAF53]/10 file:text-[#CFAF53] hover:file:bg-[#CFAF53]/20"/>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Email (Login)</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 text-sm cursor-not-allowed" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Nomor WhatsApp</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="081xxx">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-2">Jenis Kelamin</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-700">Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-700">Perempuan</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 my-6"></div>
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">📍 Alamat Pengiriman</h3>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                            <p class="text-xs text-blue-800">
                                💡 <strong>Penting:</strong> Pilih kecamatan dengan benar agar ongkir bisa dihitung otomatis saat checkout!
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- 1. PROVINSI --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Provinsi</label>
                                <select x-model="selectedProvinceId" @change="fetchCities()" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53]">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <template x-for="item in provinces" :key="item.id">
                                        <option :value="item.id" x-text="item.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- 2. KOTA --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Kota / Kabupaten</label>
                                <select x-model="selectedCityId" @change="fetchDistricts()" :disabled="!selectedProvinceId" 
                                        class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] disabled:bg-slate-100">
                                    <option value="">-- Pilih Kota --</option>
                                    <template x-for="item in cities" :key="item.id">
                                        <option :value="item.id" x-text="item.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- 3. KECAMATAN --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Kecamatan</label>
                                <select x-model="selectedDistrictId" @change="updateLocationNames()" :disabled="!selectedCityId"
                                        class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] disabled:bg-slate-100">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <template x-for="item in districts" :key="item.id">
                                        <option :value="item.id" x-text="item.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- 4. KODE POS --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Kode Pos</label>
                                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->pasienProfile?->kode_pos) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53]">
                            </div>

                            {{-- 5. ALAMAT LENGKAP --}}
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Lengkap (Jalan, RT/RW)</label>
                                <textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53]">{{ old('alamat', $user->pasienProfile?->alamat) }}</textarea>
                            </div>

                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-[#CFAF53] text-slate-900 font-bold px-8 py-3 rounded-xl hover:bg-[#b59b48] shadow-lg transition transform hover:-translate-y-1">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT ALPINE JS --}}
    <script>
    function addressSelector() {
        return {
            provinces: [],
            cities: [],
            districts: [],
            loading: false,

            // Data yang sudah tersimpan di DB
            selectedProvinceId: '{{ $user->pasienProfile->province_id ?? "" }}',
            selectedCityId: '{{ $user->pasienProfile->city_id ?? "" }}',
            selectedDistrictId: '{{ $user->pasienProfile->district_id ?? "" }}',

            selectedProvinceName: '{{ $user->pasienProfile->provinsi ?? "" }}',
            selectedCityName: '{{ $user->pasienProfile->kota ?? "" }}',
            selectedDistrictName: '{{ $user->pasienProfile->district_name ?? "" }}',

            async init() {
                console.log('🚀 Address Selector Initialized');
                await this.fetchProvinces();

                if (this.selectedProvinceId) {
                    await this.fetchCities(true); 
                    if (this.selectedCityId) {
                        await this.fetchDistricts(true);
                    }
                }
            },

            async fetchProvinces() {
                if (this.loading) return;
                this.loading = true;
                try {
                    const res = await fetch("{{ route('api.provinces') }}");
                    const data = await res.json();
                    this.provinces = Array.isArray(data) ? data : (data.data || []);
                } catch (e) {
                    console.error('❌ Error fetching provinces:', e);
                } finally {
                    this.loading = false;
                }
            },

            async fetchCities(isInit = false) {
                if (!this.selectedProvinceId) return;
                if (this.loading) return;
                this.loading = true;

                if (!isInit) {
                    this.selectedCityId = '';
                    this.selectedDistrictId = '';
                    this.cities = [];
                    this.districts = [];
                }

                const prov = this.provinces.find(p => p.id == this.selectedProvinceId);
                if (prov) this.selectedProvinceName = prov.name;

                try {
                    const res = await fetch(`/api/cities/${this.selectedProvinceId}`);
                    const data = await res.json();
                    this.cities = Array.isArray(data) ? data : (data.data || []);
                } catch (e) {
                    console.error('❌ Error fetching cities:', e);
                } finally {
                    this.loading = false;
                }
            },

            async fetchDistricts(isInit = false) {
                if (!this.selectedCityId) return;
                if (this.loading) return;
                this.loading = true;

                if (!isInit) {
                    this.selectedDistrictId = '';
                    this.districts = [];
                }

                const city = this.cities.find(c => c.id == this.selectedCityId);
                if (city) this.selectedCityName = city.name;

                try {
                    const res = await fetch(`/api/districts/${this.selectedCityId}`);
                    const data = await res.json();
                    this.districts = Array.isArray(data) ? data : (data.data || []);
                    this.updateLocationNames();
                } catch (e) {
                    console.error('❌ Error fetching districts:', e);
                } finally {
                    this.loading = false;
                }
            },

            updateLocationNames() {
                const dist = this.districts.find(d => d.id == this.selectedDistrictId);
                if (dist) this.selectedDistrictName = dist.name;
            }
        }
    }

    function appCalendar() {
        return {
            month: '',
            year: '',
            no_of_days: [],
            blankdays: [],
            days: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            dayNames: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],

            // MENGAMBIL DATA DARI VARIABEL PHP DI ATAS
            events: @json($eventsData),

            selectedDate: '',
            selectedEvents: [],

            init() {
                let today = new Date();
                this.month = today.getMonth();
                this.year = today.getFullYear();
                this.selectedDate = today.getDate();
                this.getNoOfDays();
                this.getEventsForSelectedDate();
            },

            isToday(date) {
                const today = new Date();
                const d = new Date(this.year, this.month, date);
                return today.toDateString() === d.toDateString();
            },

            hasEvent(date) {
                let dateString = new Date(this.year, this.month, date).toLocaleDateString('en-CA');
                return this.events.filter(e => e.date === dateString).length > 0;
            },

            getEvents(date) {
                let dateString = new Date(this.year, this.month, date).toLocaleDateString('en-CA');
                return this.events.filter(e => e.date === dateString);
            },

            getNoOfDays() {
                let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
                let dayOfWeek = new Date(this.year, this.month).getDay();
                
                let blankdaysArray = [];
                for (var i = 1; i <= dayOfWeek; i++) { blankdaysArray.push(i); }
                
                let daysArray = [];
                for (var i = 1; i <= daysInMonth; i++) { daysArray.push(i); }
                
                this.blankdays = blankdaysArray;
                this.no_of_days = daysArray;
            },

            selectDate(date) {
                this.selectedDate = date;
                this.getEventsForSelectedDate();
            },

            getEventsForSelectedDate() {
                if (!this.selectedDate) return;
                let dateString = new Date(this.year, this.month, this.selectedDate).toLocaleDateString('en-CA');
                this.selectedEvents = this.events.filter(e => e.date === dateString);
            },

            nextMonth() {
                this.month++;
                if (this.month > 11) { this.month = 0; this.year++; }
                this.getNoOfDays();
                this.selectDate(null);
            },

            prevMonth() {
                this.month--;
                if (this.month < 0) { this.month = 11; this.year--; }
                this.getNoOfDays();
                this.selectDate(null);
            },

            currentMonth() {
                let today = new Date();
                this.month = today.getMonth();
                this.year = today.getFullYear();
                this.getNoOfDays();
                this.selectDate(today.getDate());
            }
        }
    }
    </script>
</x-guest-layout>