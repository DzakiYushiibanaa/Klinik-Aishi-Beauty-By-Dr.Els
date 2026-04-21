<x-guest-layout title="{{ $question->title }}">
    <div class="min-h-screen bg-slate-50 pb-20">

        {{-- 1. BREADCRUMB & NAV --}}
        <div class="sticky top-0 z-30 w-full">
            <div class="mx-auto flex h-16 max-w-4xl items-center px-4">
                
                <a href="{{ route('forum.index') }}" 
                class="group flex items-center gap-3 text-sm font-medium text-slate-500 hover:text-[#CFAF53] transition-colors">
                    
                    {{-- Icon Box --}}
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm transition-all group-hover:border-[#CFAF53] group-hover:bg-[#CFAF53] group-hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </div>
                    
                    {{-- Text --}}
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-700 group-hover:text-[#CFAF53]">Kembali ke Daftar Forum</span>
                    </div>
                </a>

            </div>
        </div>

        <div class="flex flex-col gap-8 pt-8">
            
            {{-- 2. SECTION: PERTANYAAN PASIEN --}}
            <section class="mx-auto w-full max-w-4xl px-4">
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                    
                    {{-- Header Pertanyaan --}}
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-500 ring-4 ring-slate-50">
                                {{ $question->is_anonymous ? 'A' : strtoupper(substr($question->user->name, 0, 1)) }}
                            </div>
                            
                            {{-- Meta Info --}}
                            <div>
                                <h3 class="font-bold text-slate-900">
                                    {{ $question->is_anonymous ? 'Penanya Anonim' : $question->user->name }}
                                </h3>
                                <p class="text-sm text-slate-500">
                                    Diposting {{ $question->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        @php
                            $statusLabel = 'Menunggu Jawaban';
                            $statusColor = 'bg-yellow-100 text-yellow-700 border-yellow-200'; // Default: Kuning (Pending)

                            if ($question->status === 'ditutup') {
                                $statusLabel = 'Selesai / Ditutup';
                                $statusColor = 'bg-slate-100 text-slate-600 border-slate-200'; // Abu-abu (Closed)
                            } 
                            elseif ($question->answers->count() > 0) {
                                $statusLabel = 'Sudah Dijawab Dokter';
                                $statusColor = 'bg-emerald-100 text-emerald-700 border-emerald-200'; // Hijau (Success)
                            }
                        @endphp

                        <span class="self-start rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Judul --}}
                    <h1 class="mb-6 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl">
                        {{ $question->title }}
                    </h1>

                    {{-- ISI PERTANYAAN (UPDATED) --}}
                    {{-- 
                        Changes:
                        1. whitespace-pre-wrap: Agar enter dan spasi terbaca (membuat list tampil rapi).
                        2. break-words: Agar teks panjang tidak merusak layout.
                        3. text-slate-700: Warna teks agar enak dibaca.
                    --}}
                    <div class="whitespace-pre-wrap break-words text-base leading-relaxed text-slate-700">
                        {{-- Menggunakan e() untuk keamanan, karena whitespace-pre-wrap sudah menghandle format visual --}}
                        {{ $question->body }}
                    </div>

                </div>
            </section>

            {{-- CONNECTOR LINE --}}
            @if($question->answers->count() > 0)
                <div class="mx-auto h-8 w-px border-l-2 border-dashed border-slate-300"></div>
            @endif

            {{-- 3. SECTION: JAWABAN DOKTER --}}
            <section class="mx-auto w-full max-w-4xl px-4">
                
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-xl font-bold text-slate-900">
                        <svg class="h-6 w-6 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jawaban Dokter
                    </h2>
                    <span class="rounded-full bg-[#CFAF53]/10 px-3 py-1 text-xs font-bold text-[#CFAF53]">
                        {{ $question->answers->count() }} Balasan
                    </span>
                </div>

                <div class="space-y-6">
                    @forelse($question->answers as $answer)
                        {{-- Kartu Jawaban --}}
                        <div class="relative overflow-hidden rounded-2xl border border-[#CFAF53]/30 bg-gradient-to-br from-[#FFFDF5] to-white p-6 shadow-sm transition hover:shadow-md sm:p-8">
                            
                            {{-- Header Dokter --}}
                            <div class="mb-6 flex items-start gap-4 border-b border-[#CFAF53]/10 pb-4">
                                {{-- Foto/Initial Dokter --}}
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#CFAF53] text-xl font-bold text-white shadow-lg shadow-[#CFAF53]/20">
                                    {{ strtoupper(substr($answer->dokter->name, 0, 1)) }}
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold text-slate-900 text-lg">
                                            {{ $answer->dokter->name }}
                                        </p>
                                        {{-- Verified Badge --}}
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600 ring-1 ring-blue-500/10 shrink-0">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Dokter Terverifikasi
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">
                                        Dijawab pada {{ $answer->created_at->format('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                            </div>

                            {{-- ISI JAWABAN --}}
                            <div class="prose prose-slate max-w-none prose-a:text-[#CFAF53]">
                                {!! $answer->body !!}
                            </div>

                        </div>
                    @empty
                        {{-- Empty State jika belum ada jawaban --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center">
                            <p class="text-slate-500">Belum ada jawaban dari dokter untuk pertanyaan ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
</x-guest-layout>