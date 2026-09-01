{{-- Partial: Hero --}}
        {{-- SECTION: HERO (Responsive & Visual) --}}
        <section class="relative overflow-hidden bg-white pt-12 pb-16 lg:pt-24 lg:pb-32 border-b border-slate-100">
            {{-- Background Elements --}}
            <div class="pointer-events-none absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-[#CFAF53]/5 blur-3xl opacity-50"></div>
            <div class="pointer-events-none absolute bottom-0 left-0 -mb-20 -ml-20 h-[300px] w-[300px] rounded-full bg-blue-50/50 blur-3xl opacity-60"></div>

            <div class="container mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                    
                    {{-- LEFT: Text Content --}}
                    <div class="relative z-10 text-center lg:text-left">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#CFAF53]/20 bg-[#FFFDF5] px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#997B2D] shadow-sm mb-6">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                            Forum Konsultasi
                        </span>

                        <h1 class="mb-6 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                            Tanya Dokter Kulit, <br class="hidden lg:block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#CFAF53] to-[#b59b48]">
                                Cepat & Terpercaya.
                            </span>
                        </h1>

                        <p class="mb-8 text-base text-slate-600 leading-relaxed sm:text-lg lg:max-w-lg">
                            Jangan ragu dengan kondisi kulit Anda. Dapatkan diagnosa awal dan saran medis langsung dari tim dokter spesialis Aishi Beauty By Dr.Els.
                        </p>

                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center lg:justify-start">
                            @auth
                                <a href="{{ route('forum.create') }}" 
                                   class="group inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-[#CFAF53] hover:-translate-y-1 hover:shadow-[#CFAF53]/20">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span>Buat Pertanyaan</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-full bg-slate-900 px-8 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-[#CFAF53]">
                                    Login untuk Bertanya
                                </a>
                                <a href="{{ route('register') }}" class="rounded-full bg-white border border-slate-200 px-8 py-3.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 hover:text-[#CFAF53]">
                                    Daftar Akun
                                </a>
                            @endauth
                        </div>
                    </div>

                    {{-- RIGHT: Visualization (Hidden on Mobile) --}}
                    <div class="hidden lg:block relative z-10">
                         {{-- Ilustrasi Dokter & Pasien (Sama seperti sebelumnya tapi dioptimalkan ukurannya) --}}
                        <div class="relative mx-auto h-[400px] w-full max-w-[450px]">
                            {{-- Kartu Pasien --}}
                            <div class="absolute bottom-8 left-0 z-20 w-60 rounded-2xl bg-white p-4 shadow-xl border border-slate-100 animate-fade-in-up">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-9 w-9 rounded-full bg-slate-50 flex items-center justify-center text-lg">
                                        🙋‍♀️
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Pasien Baru</p>
                                        <p class="text-[10px] text-slate-400">1 menit lalu</p>
                                    </div>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2.5 text-xs text-slate-600 italic">
                                    "Wajah kusam & flek hitam, solusinya apa ya dok?"
                                </div>
                            </div>

                            {{-- Garis Koneksi --}}
                            <svg class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-32 w-56 text-[#CFAF53]/30 z-10" viewBox="0 0 200 100" fill="none">
                                <path d="M10 80 C 60 20, 140 120, 190 20" stroke="currentColor" stroke-width="2" stroke-dasharray="4 4" stroke-linecap="round">
                                     <animate attributeName="stroke-dashoffset" from="200" to="0" dur="4s" repeatCount="indefinite" />
                                </path>
                            </svg>

                            {{-- Kartu Dokter --}}
                            <div class="absolute top-4 right-0 z-20 w-64 rounded-2xl bg-white p-4 shadow-[0_15px_40px_-10px_rgba(207,175,83,0.15)] border border-[#CFAF53]/20 animate-fade-in-down" style="animation-delay: 0.2s">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="h-10 w-10 rounded-full bg-[#CFAF53] flex items-center justify-center text-white shadow-md">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Dr. Els Beauty</p>
                                        <p class="text-[10px] text-[#CFAF53] font-medium">Sedang mengetik...</p>
                                    </div>
                                </div>
                                <div class="flex gap-1 ml-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#CFAF53] animate-bounce"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#CFAF53] animate-bounce" style="animation-delay: 0.2s"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#CFAF53] animate-bounce" style="animation-delay: 0.4s"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>