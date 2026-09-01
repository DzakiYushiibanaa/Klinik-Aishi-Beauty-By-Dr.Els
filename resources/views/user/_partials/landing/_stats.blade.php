{{-- Section: Stats Counter --}}
        {{-- SECTION 2: STATS (Dark block attached below hero) --}}
        <section class="w-full bg-[#2D2A26] py-10 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-[#4A453F]" x-data="{ countTo(max, key) { 
                        let start = 0; 
                        const duration = 2000; 
                        const stepTime = Math.abs(Math.floor(duration / Math.max(max, 1))); 
                        let timer = setInterval(() => { 
                            start += Math.ceil(Math.max(max, 1) / (duration/30)); 
                            if(start >= max) { start = max; clearInterval(timer); } 
                            this[key] = start; 
                        }, 30); 
                     }, c1: 0, c2: 0, c3: 0, c4: 0 }" x-init="
                        setTimeout(() => countTo({{ (int) ($stats['pasien_terlayani'] ?? 0) }}, 'c1'), 500);
                        setTimeout(() => countTo({{ (int) ($stats['jenis_perawatan'] ?? 0) }}, 'c2'), 500);
                        setTimeout(() => countTo({{ (int) ($stats['ahli_kecantikan'] ?? 0) }}, 'c3'), 500);
                        setTimeout(() => countTo({{ max(now()->year - 2018, 1) }}, 'c4'), 500);
                     ">

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-sans text-white mb-1"><span
                                x-text="c1.toLocaleString('id-ID')">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Pasien Terlayani
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-sans text-white mb-1"><span x-text="c2">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Jenis Perawatan
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-sans text-white mb-1"><span x-text="c3">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Ahli Kecantikan
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-sans text-white mb-1"><span x-text="c4">0</span><span class="text-base align-top ml-0.5">+</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Tahun Berpengalaman</p>
                    </div>
                </div>
            </div>
        </section>
