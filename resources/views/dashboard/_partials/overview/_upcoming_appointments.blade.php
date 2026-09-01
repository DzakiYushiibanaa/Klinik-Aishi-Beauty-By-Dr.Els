{{-- Partial: Upcoming Appointments --}}
    {{-- Jadwal Mendatang --}}
    <div class="mt-8">
        <div class="dash-section">
            <div class="dash-section__head">
                <div>
                    <p class="dash-section__eyebrow">Jadwal Mendatang</p>
                    <p class="dash-section__sub">Janji temu aktif dan yang akan datang.</p>
                </div>
                <a href="{{ route('appointment.select') }}" class="dash-section__link">
                    + Janji Baru
                </a>
            </div>

            @if($upcomingAppointments->isEmpty())
                <div class="dash-empty">
                    <div aria-hidden="true" class="dash-empty__orb"></div>
                    <div class="dash-empty__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="dash-empty__title">Belum ada janji temu</p>
                    <p class="dash-empty__desc">Jadwalkan kunjungan Anda dan rasakan pengalaman perawatan premium di Aishi Beauty.</p>
                    <a href="{{ route('appointment.select') }}" class="dash-empty__cta">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Janji Pertama
                    </a>
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
