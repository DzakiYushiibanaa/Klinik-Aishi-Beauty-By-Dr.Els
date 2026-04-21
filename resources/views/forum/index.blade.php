<x-guest-layout title="Forum Konsultasi">
    <div class="min-h-screen bg-[#FAFAFA] pb-20">

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
                            <div class="absolute bottom-8 left-0 z-20 w-60 rounded-2xl bg-white p-4 shadow-[0_15px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 animate-fade-in-up">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-9 w-9 rounded-full bg-slate-50 flex items-center justify-center text-lg">🤷‍♀️</div>
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

        {{-- SECTION: STICKY SEARCH BAR (Mobile Optimized) --}}
        <div class="sticky top-0 z-30 w-full bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300">
            <div class="mx-auto max-w-6xl px-4 py-3">
                <form method="GET" action="{{ route('forum.index') }}" class="flex gap-2 sm:gap-4">
                    
                    {{-- Input Search --}}
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#CFAF53] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari keluhan (cth: jerawat)..." 
                               class="w-full rounded-xl border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53] transition placeholder:text-slate-400 hover:bg-white">
                    </div>
                    
                    {{-- Dropdown Status (Hidden on very small screens, optional) --}}
                    <div class="relative hidden sm:block w-40">
                        <select name="status" 
                                class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 pl-4 pr-10 py-2.5 text-sm text-slate-600 focus:border-[#CFAF53] focus:ring-[#CFAF53] cursor-pointer hover:bg-white transition">
                            <option value="">Semua Status</option>
                            <option value="dibuka" {{ request('status') == 'dibuka' ? 'selected' : '' }}>Dibuka</option>
                            <option value="ditutup" {{ request('status') == 'ditutup' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    
                    {{-- Search Button --}}
                    <button type="submit" 
                            class="shrink-0 rounded-xl bg-slate-900 px-4 sm:px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#CFAF53] active:scale-95">
                        <span class="hidden sm:inline">Cari</span>
                        <span class="sm:hidden"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- SECTION: QUESTIONS LIST --}}
        <section class="mx-auto w-full max-w-5xl px-4 py-8 sm:py-12">
            <div class="space-y-4">
                @forelse($questions as $question)
                    
                    @php
                        $statusClass = 'bg-slate-100 text-slate-600';
                        $statusText = 'Menunggu';
                        $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

                        if ($question->status === 'ditutup') {
                            $statusClass = 'bg-slate-200 text-slate-500';
                            $statusText = 'Ditutup';
                            $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>';
                        } elseif ($question->answers_count > 0) {
                            $statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                            $statusText = 'Dijawab';
                            $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                        }
                    @endphp

                    <a href="{{ route('forum.show', $question->forum_question_id) }}" 
                       class="group relative block rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 transition-all hover:border-[#CFAF53]/40 hover:shadow-lg hover:shadow-[#CFAF53]/5 hover:-translate-y-0.5">
                        
                        <div class="flex gap-4 items-start">
                            {{-- Avatar (Inisial) --}}
                            <div class="hidden sm:flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-500 font-bold text-sm border border-slate-100 group-hover:bg-[#CFAF53] group-hover:text-white group-hover:border-[#CFAF53] transition-colors">
                                {{ $question->is_anonymous ? '?' : strtoupper(substr($question->user->name, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                {{-- Header Card --}}
                                <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4 mb-2">
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="font-bold text-slate-800">
                                            {{ $question->is_anonymous ? 'Anonim' : $question->user->name }}
                                        </span>
                                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                        <span>{{ $question->created_at->diffForHumans() }}</span>
                                    </div>

                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                        {!! $statusIcon !!}
                                        {{ $statusText }}
                                    </span>
                                </div>

                                {{-- Judul --}}
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-[#CFAF53] transition-colors line-clamp-1 mb-1">
                                    {{ $question->title }}
                                </h3>

                                {{-- Preview Body --}}
                                <p class="text-sm text-slate-600 line-clamp-2 leading-relaxed mb-4">
                                    {{ Str::limit(strip_tags($question->body), 140) }}
                                </p>

                                {{-- Footer Card --}}
                                <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-auto">
                                    <div class="flex items-center gap-1.5 text-xs font-medium {{ $question->answers_count > 0 ? 'text-[#CFAF53]' : 'text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        {{ $question->answers_count }} Balasan Dokter
                                    </div>
                                    <div class="flex items-center gap-1 text-xs font-semibold text-slate-400 group-hover:text-[#CFAF53] transition-colors">
                                        Lihat Detail
                                        <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 py-12 text-center">
                        <div class="mb-3 rounded-full bg-white p-3 shadow-sm ring-1 ring-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Belum ada pertanyaan</h3>
                        <p class="text-sm text-slate-500 max-w-xs mt-1 mb-4">Jadilah yang pertama berkonsultasi.</p>
                        @auth
                            <a href="{{ route('forum.create') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-[#CFAF53] transition">
                                Buat Pertanyaan
                            </a>
                        @endauth
                    </div>
                @endforelse
            </div>

            @if($questions->hasPages())
                <div class="mt-8">
                    {{ $questions->links() }}
                </div>
            @endif
        </section>

    </div>
</x-guest-layout>