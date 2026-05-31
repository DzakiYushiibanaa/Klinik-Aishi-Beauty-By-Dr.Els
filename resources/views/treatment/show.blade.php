<x-guest-layout :title="$treatment->nama">
    <div class="bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">

            {{-- BREADCRUMB --}}
            <nav class="mb-8 flex items-center text-sm text-slate-500 font-medium">
                <a href="{{ route('landing') }}" class="hover:text-[#CFAF53] transition-colors">Home</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('treatment.index') }}" class="hover:text-[#CFAF53] transition-colors">Pilihan Treatment</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 truncate">{{ $treatment->nama }}</span>
            </nav>

            {{-- MAIN CONTENT (2 kolom) --}}
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">

                {{-- KOLOM KIRI: FOTO TREATMENT --}}
                <div class="mb-10 lg:mb-0">
                    <div class="relative w-full aspect-[4/5] sm:aspect-square md:aspect-[4/3] lg:aspect-square bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9] rounded-3xl overflow-hidden group border border-slate-100 shadow-sm">
                        @if ($treatment->foto_path)
                            <img src="{{ asset('storage/' . $treatment->foto_path) }}"
                                 alt="{{ $treatment->nama }}"
                                 class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-105" />
                        @else
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-300">
                                <svg class="h-20 w-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium text-slate-400">Belum ada foto treatment</span>
                            </div>
                        @endif

                        {{-- Overlay subtle --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 pointer-events-none"></div>
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL INFO --}}
                <div class="px-2 sm:px-0 flex flex-col pt-2">

                    {{-- Header: Kategori & Status --}}
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold tracking-widest text-[#CFAF53] uppercase">
                            {{ $treatment->category->nama ?? 'Treatment' }}
                        </span>

                        @if($treatment->is_active)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Tersedia
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-rose-600 ring-1 ring-inset ring-rose-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Tidak Tersedia
                            </span>
                        @endif
                    </div>

                    {{-- Judul & Harga --}}
                    <div class="mb-6">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                            {{ $treatment->nama }}
                        </h1>
                        <p class="text-3xl font-bold text-[#CFAF53]">
                            Rp {{ number_format((int)$treatment->harga, 0, ',', '.') }}
                            <span class="text-sm font-medium text-slate-400 ml-1 uppercase tracking-wider">/ sesi</span>
                        </p>
                    </div>

                    {{-- Spec ringkas (durasi) --}}
                    @if($treatment->durasi_menit)
                        <div class="mb-8 flex flex-wrap gap-4 border-y border-slate-100 py-4">
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Durasi: <span class="font-medium text-slate-900">{{ $treatment->durasi_menit }} menit</span></span>
                            </div>
                        </div>
                    @endif

                    {{-- Deskripsi --}}
                    <div class="mb-10 flex-1">
                        <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-4">Tentang Perawatan</h3>
                        <div class="prose prose-slate prose-sm sm:prose-base text-slate-600 leading-relaxed text-justify">
                            {!! nl2br(e($treatment->deskripsi ?? 'Belum ada deskripsi untuk treatment ini.')) !!}
                        </div>
                    </div>

                    {{-- Action: Buat Janji Temu --}}
                    <div class="mt-auto border-t border-slate-100 pt-8">
                        @if($treatment->is_active)
                            <a href="{{ route('appointment.create', ['treatment_id' => $treatment->treatment_id]) }}"
                               class="w-full flex h-14 items-center justify-center gap-2 rounded-xl bg-slate-900 px-8 font-bold text-white transition-all duration-300 hover:bg-[#CFAF53] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-[#CFAF53]/30">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Buat Janji Temu</span>
                            </a>
                            <p class="mt-3 text-center w-full text-xs text-slate-400">
                                Estimasi pelayanan <span class="font-semibold text-slate-600">{{ $treatment->durasi_menit ?? '60' }} menit</span>
                            </p>
                        @else
                            <div class="flex h-14 items-center justify-center rounded-xl bg-slate-100 px-8 font-bold text-slate-400 cursor-not-allowed border border-slate-200 w-full">
                                Saat ini belum bisa dipesan
                            </div>
                        @endif
                    </div>

                    {{-- Back link --}}
                    <div class="mt-8 flex justify-center items-center gap-6">
                        <a href="{{ route('treatment.index') }}" class="group flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                            <svg class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke Pilihan Treatment
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
