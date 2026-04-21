<x-guest-layout :title="$treatment->nama">
    <div class="bg-[#FFFDF8] min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
            
            {{-- BREADCRUMB --}}
            <nav class="mb-10 sm:mb-14 flex justify-center items-center text-sm text-[#8C7A63] font-medium whitespace-nowrap overflow-x-auto border-b border-[#D6C5AD]/30 pb-4">
                <a href="{{ route('landing') }}" class="hover:text-[#A3815D] transition-colors">Home</a>
                <svg class="h-4 w-4 mx-2 text-[#D6C5AD] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="{{ route('treatment.index') }}" class="hover:text-[#A3815D] transition-colors">Pilihan Treatment</a>
                <svg class="h-4 w-4 mx-2 text-[#D6C5AD] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-[#6D5A42] font-semibold truncate">{{ $treatment->nama }}</span>
            </nav>

            {{-- HEADER: KATEGORI & STATUS --}}
            <div class="flex flex-col sm:flex-row items-center justify-center mb-6 gap-3 sm:gap-4">
                <span class="text-xs font-bold tracking-[0.2em] text-[#A3815D] uppercase">
                    {{ $treatment->category->nama ?? 'Face Treatment' }}
                </span>
                
                <span class="hidden sm:block h-1 w-1 rounded-full bg-[#D6C5AD]"></span>
                
                @if($treatment->is_active)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-600 ring-1 ring-inset ring-emerald-500/20 shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Tersedia
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-rose-600 ring-1 ring-inset ring-rose-500/20 shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Tidak Tersedia
                    </span>
                @endif
            </div>

            {{-- HEADER: JUDUL & HARGA --}}
            <div class="text-center mb-12">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-bold text-[#6D5A42] tracking-tight leading-[1.1] mb-6 drop-shadow-sm">
                    {{ $treatment->nama }}
                </h1>
                <div class="inline-block py-2 px-6 bg-[#E8DCCB]/30 rounded-2xl border border-[#D6C5AD]/30 shadow-sm">
                    <p class="text-2xl sm:text-3xl font-bold text-[#A3815D]">
                        Rp {{ number_format((int)$treatment->harga, 0, ',', '.') }}
                        <span class="text-xs sm:text-sm font-medium text-[#8C7A63] ml-1 uppercase tracking-wider">/ sesi</span>
                    </p>
                </div>
            </div>

            {{-- FOTO TREATMENT (Lebih Besar & Rata Tengah) --}}
            <div class="mb-14 flex justify-center w-full">
                <div class="relative w-full max-w-3xl aspect-[4/3] sm:aspect-[16/9] bg-[#E8DCCB]/20 rounded-[2.5rem] overflow-hidden group shadow-xl border border-[#D6C5AD]/50">
                    @if ($treatment->foto_path)
                        <img src="{{ asset('storage/' . $treatment->foto_path) }}" 
                             alt="{{ $treatment->nama }}" 
                             class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-105" />
                    @else
                        {{-- Placeholder --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-[#D6C5AD]">
                            <svg class="h-20 w-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium">Belum ada foto treatment</span>
                        </div>
                    @endif
                    
                    {{-- Overlay Subtle --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 pointer-events-none"></div>
                </div>
            </div>

            {{-- KONTEN DESKRIPSI & DURASI --}}
            <div class="max-w-2xl mx-auto text-center">
                
                {{-- Durasi Box --}}
                <div class="inline-flex items-center gap-2 bg-white px-6 py-3 rounded-2xl shadow-sm border border-[#D6C5AD]/50 mb-12 text-sm text-[#6D5A42]">
                    <svg class="h-5 w-5 text-[#A3815D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Estimasi Waktu Pelayanan: <span class="font-bold">{{ $treatment->durasi_menit ?? '60' }} Menit</span></span>
                </div>

                {{-- Tentang Perawatan --}}
                <div class="mb-14">
                    <h3 class="text-xs font-bold tracking-[0.2em] text-[#CFAF53] uppercase mb-6 flex items-center justify-center gap-3">
                        <span class="h-px w-8 bg-[#CFAF53]/50"></span>
                        Tentang Perawatan Ini
                        <span class="h-px w-8 bg-[#CFAF53]/50"></span>
                    </h3>
                    <div class="prose prose-slate prose-sm sm:prose-base text-[#8C7A63] leading-loose mx-auto">
                        {!! nl2br(e($treatment->deskripsi ?? 'Belum ada deskripsi untuk treatment ini.')) !!}
                    </div>
                </div>

                {{-- ACTION AREA --}}
                <div class="pt-10 border-t border-[#D6C5AD]/30 flex flex-col items-center">
                    @if($treatment->is_active)
                        <a href="{{ route('appointment.create', ['treatment_id' => $treatment->treatment_id]) }}" 
                           class="flex h-14 w-full max-w-[320px] items-center justify-center gap-2 rounded-full bg-[#A3815D] px-8 font-bold text-white transition-all duration-300 hover:bg-[#8C7A63] hover:shadow-xl hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-[#A3815D]/30 shadow-lg shadow-[#A3815D]/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Booking Sekarang</span>
                        </a>
                    @else
                        <div class="flex h-14 w-full max-w-[320px] items-center justify-center rounded-full bg-slate-100 px-8 font-bold text-slate-400 cursor-not-allowed border border-slate-200">
                            Saat ini belum bisa dipesan
                        </div>
                    @endif
                    
                    <a href="{{ route('treatment.index') }}" class="group flex items-center mt-8 text-sm font-medium text-[#A3815D] hover:text-[#6D5A42] transition-colors">
                        <svg class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Pilihan Treatment
                    </a>
                </div>

            </div>

        </div>
    </div>
</x-guest-layout>