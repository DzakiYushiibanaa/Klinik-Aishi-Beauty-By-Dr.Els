{{-- ====== TAB: OVERVIEW ====== --}}
<div class="space-y-6">

    {{-- Greeting + CTA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-2xl text-[#8C7A63] font-medium">Selamat Datang,</p>
            <h1 class="text-2xl md:text-3xl font-extrabold text-[#1A1A1A] tracking-tight">{{ $user->name }} 👋</h1>
        </div>
        <a href="{{ route('appointment.select') }}" class="inline-flex items-center gap-2 bg-[#CFAF53] text-[#1A1A1A] font-bold px-6 py-3 rounded-full shadow-lg shadow-[#CFAF53]/20 hover:bg-[#b59b48] transition text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Buat Janji Temu
        </a>
    </div>

    {{-- Section A: Pending Payments (Priority Action) --}}
    @if(isset($pendingPayments) && count($pendingPayments) > 0)
        @foreach($pendingPayments as $payment)
            @if($payment['type'] === 'treatment')
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-6 overflow-hidden">
                    <!-- HEADER -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 gap-3">
                        <div class="flex items-center gap-3">
                            <h2 class="text-[16px] font-bold text-slate-900">Pembayaran Treatment</h2>
                            <span class="text-[11px] font-medium text-slate-500 bg-white px-2 py-1 rounded border border-slate-200 tracking-wide">{{ $payment['order']->kode_order }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Belum Bayar</span>
                            @if($payment['order']->expired_at)
                            <div x-data="countdownTimer('{{ $payment['order']->expired_at }}')" class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div class="flex items-center text-rose-600 font-bold text-[13px] tabular-nums tracking-tighter">
                                    <template x-if="showHours">
                                        <div class="flex items-center">
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                        </div>
                                    </template>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div class="p-4 sm:p-5 flex flex-col gap-4">
                        <!-- Jadwal Info -->
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 rounded-xl bg-slate-50 flex-shrink-0 overflow-hidden border border-slate-200 shadow-sm flex items-center justify-center">
                                <svg class="w-7 h-7 text-[#D4A017]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-[15px] font-bold text-slate-900 mb-1.5">Jadwal Booking</h3>
                                <div class="flex items-center gap-1.5">
                                    <p class="text-sm font-semibold text-slate-700">{{ $payment['appointment']->tanggal_jam->translatedFormat('d F Y • H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Treatment Items -->
                        @foreach($payment['order']->items as $item)
                            <div class="flex gap-4 items-center">
                                @php
                                    $image = null;
                                    if ($item->treatment && $item->treatment->foto_path) {
                                        $image = asset('storage/' . $item->treatment->foto_path);
                                    } elseif ($item->bundle && $item->bundle->foto_path) {
                                        $image = asset('storage/' . $item->bundle->foto_path);
                                    }
                                @endphp
                                
                                <div class="w-16 h-16 rounded-xl bg-slate-50 flex-shrink-0 overflow-hidden border border-slate-200 shadow-sm flex items-center justify-center">
                                    @if($image)
                                        <img src="{{ $image }}" alt="{{ $item->treatment?->nama ?? 'Treatment' }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-[15px] font-bold text-slate-900 mb-1.5 line-clamp-1">{{ $item->treatment?->nama ?? $item->bundle?->nama ?? 'Treatment' }}</h3>
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                        @if($item->qty > 1)
                                            <span class="text-[11px] text-slate-400 font-medium">x{{ $item->qty }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- FOOTER -->
                    <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-start gap-2.5 bg-amber-50/80 px-4 py-3 rounded-xl border border-amber-200/50 w-full sm:w-auto flex-1 shadow-sm">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="text-xs font-medium text-amber-800 leading-relaxed">Jika pembayaran tidak diselesaikan, jadwal akan dibatalkan otomatis dan slot akan dibuka kembali.</span>
                        </div>
                        
                        <button @click="processPayment('{{ $payment['order']->kode_order }}', $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#D4A017] hover:bg-[#b58812] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#D4A017]/20">
                            Bayar Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-6 overflow-hidden">
                    <!-- HEADER -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 gap-3">
                        <div class="flex items-center gap-3">
                            <h2 class="text-[16px] font-bold text-slate-900">Pembayaran Produk</h2>
                            <span class="text-[11px] font-medium text-slate-500 bg-white px-2 py-1 rounded border border-slate-200 tracking-wide">{{ $payment['order']->kode_order }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Belum Bayar</span>
                            @if($payment['order']->expired_at)
                            <div x-data="countdownTimer('{{ $payment['order']->expired_at }}')" class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div class="flex items-center text-rose-600 font-bold text-[13px] tabular-nums tracking-tighter">
                                    <template x-if="showHours">
                                        <div class="flex items-center">
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                        </div>
                                    </template>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div class="p-4 sm:p-5 flex flex-col gap-4">
                        @foreach($payment['order']->items->take(2) as $item)
                            <div class="flex gap-4 items-center">
                                @php
                                    $image = null;
                                    if ($item->product && $item->product->foto_path) {
                                        $image = asset('storage/' . $item->product->foto_path);
                                    } elseif ($item->bundle && $item->bundle->foto_path) {
                                        $image = asset('storage/' . $item->bundle->foto_path);
                                    } else {
                                        $image = asset('images/placeholder.jpg'); // Fallback
                                    }
                                @endphp
                                <div class="w-16 h-16 rounded-xl bg-white flex-shrink-0 overflow-hidden border border-slate-200 shadow-sm flex items-center justify-center">
                                    <img src="{{ $image }}" alt="{{ $item->product?->nama ?? 'Produk' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-[15px] font-bold text-slate-900 mb-1.5 line-clamp-1">{{ $item->product?->nama ?? $item->bundle?->nama ?? 'Produk' }}</h3>
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                        <span class="text-[11px] text-slate-400 font-medium">x{{ $item->qty }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($payment['order']->items->count() > 2)
                            <p class="text-xs font-semibold text-slate-500 italic pl-[80px]">+ {{ $payment['order']->items->count() - 2 }} produk lainnya</p>
                        @endif
                    </div>

                    <!-- FOOTER -->
                    <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-start gap-2.5 bg-amber-50/80 px-4 py-3 rounded-xl border border-amber-200/50 w-full sm:w-auto flex-1 shadow-sm">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="text-xs font-medium text-amber-800 leading-relaxed">Jika pembayaran tidak diselesaikan, pesanan batal otomatis.</span>
                        </div>
                        
                        <button @click="processPayment('{{ $payment['order']->kode_order }}', $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#D4A017] hover:bg-[#b58812] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#D4A017]/20">
                            Bayar Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- Section A: Lanjutkan Aktivitas (Other logic) --}}
    @if(isset($nextAction))
        @php
            $naColors = match ($nextAction['color']) {
                'amber' => [
                    'bg' => 'from-amber-50 to-white',
                    'border' => 'border-amber-200',
                    'icon_bg' => 'bg-amber-100',
                    'icon_text' => 'text-amber-600',
                    'btn_sec_text' => 'text-amber-700',
                    'btn_sec_border' => 'border-amber-200 hover:bg-amber-50',
                    'btn_pri_bg' => 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20',
                ],
                'orange' => [
                    'bg' => 'from-orange-50 to-white',
                    'border' => 'border-orange-200',
                    'icon_bg' => 'bg-orange-100',
                    'icon_text' => 'text-orange-600',
                    'btn_sec_text' => 'text-orange-700',
                    'btn_sec_border' => 'border-orange-200 hover:bg-orange-50',
                    'btn_pri_bg' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                ],
                'blue' => [
                    'bg' => 'from-blue-50 to-white',
                    'border' => 'border-blue-200',
                    'icon_bg' => 'bg-blue-100',
                    'icon_text' => 'text-blue-600',
                    'btn_sec_text' => 'text-blue-700',
                    'btn_sec_border' => 'border-blue-200 hover:bg-blue-50',
                    'btn_pri_bg' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
                ],
                'emerald' => [
                    'bg' => 'from-emerald-50 to-white',
                    'border' => 'border-emerald-200',
                    'icon_bg' => 'bg-emerald-100',
                    'icon_text' => 'text-emerald-600',
                    'btn_sec_text' => 'text-emerald-700',
                    'btn_sec_border' => 'border-emerald-200 hover:bg-emerald-50',
                    'btn_pri_bg' => 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20',
                ],
                default => [
                    'bg' => 'from-slate-50 to-white',
                    'border' => 'border-slate-200',
                    'icon_bg' => 'bg-slate-100',
                    'icon_text' => 'text-slate-600',
                    'btn_sec_text' => 'text-slate-700',
                    'btn_sec_border' => 'border-slate-200 hover:bg-slate-50',
                    'btn_pri_bg' => 'bg-slate-600 hover:bg-slate-700 shadow-slate-600/20',
                ]
            };
        @endphp
        <div class="bg-gradient-to-r {{ $naColors['bg'] }} rounded-2xl border {{ $naColors['border'] }} shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full {{ $naColors['icon_bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 {{ $naColors['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $nextAction['icon'] !!}</svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-[#1A1A1A]">{{ $nextAction['title'] }}</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ $nextAction['description'] }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                @if(isset($nextAction['secondary_cta_text']))
                    <a href="{{ $nextAction['secondary_cta_link'] }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-full font-bold text-sm {{ $naColors['btn_sec_text'] }} bg-white border {{ $naColors['btn_sec_border'] }} transition-colors">
                        {{ $nextAction['secondary_cta_text'] }}
                    </a>
                @endif

                @if(isset($nextAction['cta_tab']))
                    <button @click="activeTab = '{{ $nextAction['cta_tab'] }}'" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-full font-bold text-sm text-white {{ $naColors['btn_pri_bg'] }} transition-colors shadow-lg">
                        {{ $nextAction['cta_text'] }}
                    </button>
                @elseif(isset($nextAction['cta_link']))
                    <a href="{{ $nextAction['cta_link'] }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-full font-bold text-sm text-white {{ $naColors['btn_pri_bg'] }} transition-colors shadow-lg">
                        {{ $nextAction['cta_text'] }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-[#CFAF53]/40 shadow-sm shadow-[#CFAF53]/10 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#CFAF53]/5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-[#CFAF53]/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#CFAF53] bg-[#CFAF53]/10 px-2 py-1 rounded">Aktif</span>
            </div>
            <div class="relative z-10">
                <p class="text-5xl md:text-6xl font-black text-[#1A1A1A] tracking-tighter">{{ $stats['upcoming'] }}</p>
                <p class="text-base md:text-lg font-extrabold text-slate-600 mt-2">Janji Temu Aktif</p>
                <p class="text-xs md:text-sm text-slate-400 mt-1">Jadwal menunggu & disetujui</p>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <p class="text-5xl md:text-6xl font-black text-[#1A1A1A] tracking-tighter">{{ $stats['active_orders'] }}</p>
            <p class="text-base md:text-lg font-extrabold text-slate-600 mt-2">Pesanan Aktif</p>
            <p class="text-xs md:text-sm text-slate-400 mt-1">Menunggu bayar & diproses</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6 col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-5xl md:text-6xl font-black text-[#1A1A1A] tracking-tighter">{{ $stats['total_rekam_medis'] }}</p>
            <p class="text-base md:text-lg font-extrabold text-slate-600 mt-2">Kunjungan Selesai</p>
            <p class="text-xs md:text-sm text-slate-400 mt-1">Total riwayat perawatan</p>
        </div>
    </div>

    {{-- Jadwal Mendatang --}}
    <div class="mt-8">
        <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4">Jadwal Mendatang</h3>

            @if($upcomingAppointments->isEmpty())
                <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-8 text-center flex flex-col items-center justify-center">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm">
                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-[#1A1A1A]">Jadwal Kosong</p>
                    <p class="text-xs text-slate-500 mt-1 mb-4">Anda belum memiliki jadwal janji temu.</p>
                    <a href="{{ route('appointment.select') }}" class="text-xs font-bold text-[#CFAF53] bg-white border border-[#CFAF53]/20 px-4 py-2 rounded-full hover:bg-[#CFAF53]/5 transition-colors">Buat Janji →</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($upcomingAppointments as $upcoming)
                        @php
                            $treatmentNames = $upcoming->items->map(fn($i) => $i->treatment?->nama)->filter()->implode(', ');
                            $isDisetujui = strtolower($upcoming->status) === 'disetujui';
                            $statusLabel = ucfirst(strtolower($upcoming->status));
                        @endphp
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-[#E8E0D8] bg-white p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-[#CFAF53]/60 transition-all cursor-pointer"
                             @click="activeTab = 'appointment'">
                            {{-- Side accent --}}
                            <div class="absolute top-0 left-0 w-1 h-full {{ $isDisetujui ? 'bg-emerald-400' : 'bg-amber-400' }} group-hover:w-1.5 transition-all"></div>

                            <div class="flex gap-4 pl-2">
                                {{-- Calendar Date Box --}}
                                <div class="w-14 h-[68px] sm:w-16 sm:h-[72px] shrink-0 bg-[#FAF8F5] rounded-xl border border-[#E8E0D8] overflow-hidden flex flex-col shadow-sm group-hover:border-[#CFAF53]/40 transition-colors">
                                    <div class="bg-[#1A1A1A] py-1 text-center">
                                        <span class="text-[10px] font-bold text-[#CFAF53] uppercase tracking-widest">{{ $upcoming->tanggal_jam->translatedFormat('M') }}</span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center bg-white">
                                        <span class="text-2xl font-black text-[#1A1A1A] leading-none">{{ $upcoming->tanggal_jam->translatedFormat('d') }}</span>
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-[15px] text-[#1A1A1A] line-clamp-1 group-hover:text-[#CFAF53] transition-colors mb-1.5"
                                        title="{{ $treatmentNames ?: ucfirst($upcoming->jenis) }}">
                                        {{ $treatmentNames ?: ucfirst($upcoming->jenis) }}
                                    </h4>
                                    <div class="space-y-1 text-xs text-slate-500 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#CFAF53] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="whitespace-nowrap">{{ $upcoming->tanggal_jam->translatedFormat('H:i') }} WIB</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="truncate" title="{{ $upcoming->dokter->name ?? 'Dokter' }}">{{ $upcoming->dokter->name ?? 'Dokter' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer: status + CTA, full row, jarak konsisten --}}
                            <div class="mt-4 pt-3 pl-2 border-t border-[#E8E0D8]/40 flex items-center justify-between gap-3">
                                <span class="shrink-0 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest border whitespace-nowrap {{ $isDisetujui ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200' }}">
                                    {{ $statusLabel }}
                                </span>

                                <button type="button" class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-full bg-[#FAF8F5] border border-[#E8E0D8] hover:bg-white hover:border-[#CFAF53] px-3.5 py-1.5 text-xs font-bold text-[#1A1A1A] transition-colors shadow-sm whitespace-nowrap cursor-pointer">
                                    Lihat Detail
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Main: Aktivitas Terkini --}}
    <div class="mt-8">
        <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4">Aktivitas Terkini</h3>
            
            <div class="space-y-4">
            @php $latestPraKonsul = $praKonsuls->first(); @endphp
            @php $latestOrder = $orders->first(); @endphp

            {{-- Card: Pra Konsultasi Terbaru --}}
            @if($latestPraKonsul)
                @php
                    $isReviewed = in_array(strtolower($latestPraKonsul->status), ['reviewed', 'direkomendasikan']);
                    $praStatusLabel = $isReviewed ? 'DIREKOMENDASIKAN' : ($latestPraKonsul->status ?? 'SUBMITTED');
                    $praStatusClass = match (strtolower($latestPraKonsul->status ?? 'submitted')) {
                        'submitted', 'menunggu' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'reviewed', 'direkomendasikan', 'selesai' => 'bg-blue-600 text-white border-blue-700 shadow-sm shadow-blue-600/20',
                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                    };
                @endphp
                <div class="bg-[#FAF8F5] rounded-2xl border {{ $isReviewed ? 'border-blue-200 ring-4 ring-blue-50' : 'border-[#E8E0D8]/60' }} p-5 relative overflow-hidden transition-all">
                    @if($isReviewed)
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                    @endif
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 relative z-10">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl {{ $isReviewed ? 'bg-blue-100 text-blue-600' : 'bg-[#CFAF53]/10 text-[#CFAF53]' }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $isReviewed ? 'Hasil Konsultasi Tersedia' : 'Tinjauan Konsultasi' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $praStatusClass }} uppercase">
                                        {{ $praStatusLabel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Diajukan pada {{ $latestPraKonsul->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </p>

                                @if($isReviewed)
                                    <div class="mt-3 bg-white px-3.5 py-2.5 rounded-xl border border-blue-100 shadow-sm border-l-4 border-l-blue-500">
                                        <p class="text-sm font-medium text-slate-700">
                                            Dokter telah memberikan rekomendasi untuk kondisi kamu.
                                        </p>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $latestPraKonsul->keluhan_utama ?? 'Sedang ditinjau oleh tim dokter.' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div x-data="{ showLatestDetail: false }" class="relative z-10">
                        <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                            @if($isReviewed)
                                <button @click="showLatestDetail = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-blue-600/20">
                                    Lihat Rekomendasi
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            @else
                                <button @click="showLatestDetail = true" class="text-sm font-bold text-[#8C7A63] hover:text-[#1A1A1A] transition-colors">Lihat Detail →</button>
                            @endif
                        </div>

                        {{-- Modal: Detail Pra-Konsultasi Terbaru --}}
                        <template x-teleport="body">
                            <div x-show="showLatestDetail" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-[#1A1A1A]/40 backdrop-blur-sm"
                                 style="display: none;">

                                <div @click.away="showLatestDetail = false" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     class="bg-white w-full max-w-2xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">

                                    {{-- Modal Header --}}
                                    <div class="px-6 py-5 border-b border-[#E8E0D8]/60 flex items-center justify-between shrink-0 bg-white">
                                        <div>
                                            <h3 class="text-xl font-extrabold text-[#1A1A1A]">Detail Pra-Konsultasi</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">#PK-{{ $latestPraKonsul->patient_form_id }} • {{ $latestPraKonsul->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                        </div>
                                        <button @click="showLatestDetail = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Modal Body --}}
                                    <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                                        {{-- Row 1: Vital Stats --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Golongan Darah</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->golongan_darah ?? '-' }}</p>
                                            </div>
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Kulit</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->jenis_kulit ?? '-' }}</p>
                                            </div>
                                        </div>

                                        {{-- Skincare --}}
                                        <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Skincare Sebelumnya</p>
                                            <p class="text-sm text-[#1A1A1A] font-medium leading-relaxed">{{ $latestPraKonsul->skincare_sebelumnya ?? '-' }}</p>
                                        </div>

                                        {{-- Riwayat Perawatan --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">Riwayat Perawatan</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                @php
                                                    $perawatan = [
                                                        ['label' => 'Facial', 'value' => $latestPraKonsul->pernah_facial],
                                                        ['label' => 'Skin Booster', 'value' => $latestPraKonsul->pernah_skin_booster],
                                                        ['label' => 'Laser', 'value' => $latestPraKonsul->pernah_laser],
                                                        ['label' => 'Inject/Infus', 'value' => $latestPraKonsul->pernah_inject_infus],
                                                    ];
                                                @endphp
                                                @foreach($perawatan as $p)
                                                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[#E8E0D8]/60 {{ $p['value'] ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50' }}">
                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center {{ $p['value'] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                @if($p['value'])
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-bold {{ $p['value'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ $p['label'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Foto Wajah --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">📸 Foto Wajah</h4>
                                            <div class="grid grid-cols-3 gap-3">
                                                @foreach(['foto_depan_path' => 'Depan', 'foto_kiri_path' => 'Kiri', 'foto_kanan_path' => 'Kanan'] as $field => $pos)
                                                    @php $path = $latestPraKonsul->$field; @endphp
                                                    <div class="text-center">
                                                        <div class="aspect-square rounded-2xl border border-[#E8E0D8]/60 overflow-hidden bg-slate-50 mb-1.5 shadow-sm">
                                                            @if($path)
                                                                <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                                                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    <span class="text-[10px] font-bold uppercase tracking-wider">No Image</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $pos }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Rekomendasi Dokter --}}
                                        @if($latestPraKonsul->rekomendasi_dokter || $latestPraKonsul->rekomendasiItems->isNotEmpty())
                                            <div class="space-y-4 border-t border-[#E8E0D8]/60 mt-4 pt-4">
                                                
                                                {{-- Dokter's General Note --}}
                                                @if($latestPraKonsul->rekomendasi_dokter)
                                                    <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm relative overflow-hidden group">
                                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CFAF53] rounded-l-xl"></div>
                                                        <div class="flex items-center gap-2 mb-2 pl-2">
                                                            <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                            <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Rekomendasi Dokter</p>
                                                        </div>
                                                        <p class="text-sm text-[#1A1A1A] leading-relaxed pl-2">{{ $latestPraKonsul->rekomendasi_dokter }}</p>
                                                    </div>
                                                @endif
    
                                                {{-- Produk/Treatment Iteration --}}
                                                @if($latestPraKonsul->rekomendasiItems->isNotEmpty())
                                                    @foreach($latestPraKonsul->rekomendasiItems as $item)
                                                        @php 
                                                            $tipe = $item->item_tipe;
                                                            $obj = $tipe === 'Produk' ? $item->product : ($tipe === 'Treatment' ? $item->treatment : $item->bundle);
                                                        @endphp
                                                        <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm hover:border-[#CFAF53]/40 transition-colors relative overflow-hidden">
                                                            <div class="flex items-center gap-2 mb-4 border-b border-[#E8E0D8]/60 pb-3">
                                                                @if($tipe === 'Treatment')
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Treatment Rekomendasi</p>
                                                                @elseif($tipe === 'Bundle')
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Bundle Rekomendasi</p>
                                                                @else
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Produk Rekomendasi</p>
                                                                @endif
                                                            </div>
    
                                                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                                                {{-- Image --}}
                                                                <div class="w-20 h-20 rounded-lg bg-[#FAF8F5] border border-[#E8E0D8] shrink-0 overflow-hidden flex items-center justify-center">
                                                                    @if($obj && isset($obj->foto_path) && $obj->foto_path)
                                                                        <img src="{{ asset('storage/' . $obj->foto_path) }}" alt="{{ $item->item_nama }}" class="w-full h-full object-cover">
                                                                    @else
                                                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    @endif
                                                                </div>
    
                                                                {{-- Details --}}
                                                                <div class="flex-1 min-w-0 flex flex-col justify-between h-full w-full">
                                                                    <div>
                                                                        <p class="font-bold text-[#1A1A1A] text-base leading-tight mb-1">{{ $item->item_nama }}</p>
                                                                        
                                                                        @if($item->catatan)
                                                                            <p class="text-sm text-slate-600 mb-2">{{ $item->catatan }}</p>
                                                                        @endif
    
                                                                        @if($tipe === 'Treatment' && isset($obj->durasi_menit))
                                                                            <p class="text-xs font-medium text-slate-500 mb-2">{{ $obj->durasi_menit }} menit</p>
                                                                        @endif
                                                                        
                                                                        @if($obj && (isset($obj->harga) || isset($obj->harga_promo) || isset($obj->harga_normal)))
                                                                            <p class="text-sm font-bold text-[#CFAF53]">Rp {{ number_format($obj->harga ?? $obj->harga_promo ?? $obj->harga_normal ?? 0, 0, ',', '.') }}</p>
                                                                        @endif
                                                                    </div>
    
                                                                    {{-- Actions --}}
                                                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                                                        @if($tipe === 'Treatment')
                                                                            <a href="{{ route('appointment.select') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all w-full sm:w-auto text-center">Buat Janji Temu</a>
                                                                        @else
                                                                            <form action="{{ $tipe === 'Bundle' ? route('cart.add_bundle', $obj->bundle_id ?? 0) : route('cart.add', $obj->product_id ?? 0) }}" method="POST" class="inline-block flex-1 sm:flex-none">
                                                                                @csrf
                                                                                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm">+ Keranjang</button>
                                                                            </form>
                                                                            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all flex-1 sm:flex-none text-center">Beli Sekarang</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="p-6 border-t border-[#E8E0D8]/60 bg-slate-50/50 shrink-0">
                                        <button @click="showLatestDetail = false" class="w-full bg-[#1A1A1A] text-white font-bold py-4 rounded-2xl hover:bg-black transition-colors shadow-lg shadow-black/10">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endif

            {{-- Card: Pesanan Terbaru --}}
            @if($latestOrder)
                <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                    @php
                        // Deteksi apakah order ini terkait janji temu (treatment/konsultasi)
                        $linkedAppointmentId = $latestOrder->items->firstWhere('appointment_id', '!=', null)?->appointment_id;
                        $isAppointmentBased = $linkedAppointmentId
                            || in_array($latestOrder->order_type, ['treatment', 'konsultasi']);

                        $shipIcon = $isAppointmentBased
                            ? '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                            : match ($latestOrder->shipping_status) {
                                'dikirim' => '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1m0 0V6a1 1 0 011-1h2a1 1 0 011 1v4l4 2v6h-3m-7 0h7m-7 0a2 2 0 11-4 0m11 0a2 2 0 11-4 0"/></svg>',
                                'selesai' => '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                default => '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            };
                        $orderStatusLabel = match ($latestOrder->status) {
                            'menunggu_pembayaran' => 'Belum Bayar',
                            'sudah_dibayar', 'lunas' => match ($latestOrder->shipping_status) {
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai',
                                    default => 'Dikemas',
                                },
                            'batal', 'dibatalkan' => 'Dibatalkan',
                            default => ucfirst($latestOrder->status),
                        };
                        $orderStatusBadge = match ($orderStatusLabel) {
                            'Belum Bayar' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Dikemas' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Dikirim' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                                {!! $shipIcon !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $latestOrder->items->first()?->product?->nama ?? $latestOrder->items->first()?->treatment?->nama ?? 'Pesanan Aishi Beauty' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $orderStatusBadge }} uppercase">
                                        {{ $orderStatusLabel }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-mono">{{ $latestOrder->kode_order }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs text-slate-400">{{ $latestOrder->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                        @if($isAppointmentBased)
                            <button
                                @click="activeTab = 'appointment'; @if($linkedAppointmentId) highlightId = '{{ $linkedAppointmentId }}'; setTimeout(() => highlightId = null, 4000); @endif"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Janji Temu
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @else
                            <button @click="activeTab = 'orders'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Pesanan
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if(!$latestPraKonsul && !$latestOrder)
                <div class="py-12 flex flex-col items-center justify-center text-center bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8]">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-[#1A1A1A]">Belum ada aktivitas terkini</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Riwayat konsultasi dan pesanan Anda akan muncul di sini.</p>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

