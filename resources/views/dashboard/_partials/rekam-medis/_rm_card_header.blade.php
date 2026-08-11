{{-- Partial: RM Card Header --}}
                    {{-- â”€â”€â”€ 1. HEADER REKAM MEDIS â”€â”€â”€ --}}
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-[#FAF8F5] border border-[#E8E0D8] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rekam Medis</p>
                                    <p class="text-base font-extrabold text-[#1A1A1A] font-mono mt-0.5">{{ $rm->no_kunjungan }}</p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $tanggalKunjungan }}
                                        </span>
                                        @if($rm->dokter)
                                            <span class="text-slate-300">Â·</span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span class="font-medium text-[#1A1A1A]">{{ $rm->dokter->name }}</span>
                                            </span>
                                        @endif
                                        @if($rm->terapis)
                                            <span class="text-slate-300">Â·</span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Terapis</span>
                                                <span class="font-medium text-[#1A1A1A]">{{ $rm->terapis->name }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Toggle Detail --}}
                            <button @click="expanded = !expanded" class="text-sm font-bold text-[#CFAF53] hover:text-[#b09444] transition-colors inline-flex items-center gap-1 cursor-pointer self-start">
                                <span x-text="expanded ? 'Tutup Detail' : 'Lihat Detail'"></span>
                                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        {{-- â”€â”€â”€ 2. RINGKASAN LAYANAN (chips) â”€â”€â”€ --}}
                        @if($consultationNames->isNotEmpty() || $treatmentNames->isNotEmpty())
                            <div class="mt-5 pt-5 border-t border-[#E8E0D8]/60">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Layanan yang Dilakukan</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($consultationNames as $name)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                    @foreach($treatmentNames as $name)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#FAF8F5] text-[#1A1A1A] text-xs font-semibold border border-[#E8E0D8]">
                                            <svg class="w-3 h-3 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
