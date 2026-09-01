{{-- Partial: Treatment Card --}}
                        <article class="group/card relative flex flex-col rounded-2xl bg-white border border-[#E8E0D8]/60 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#CFAF53]/10 hover:border-[#CFAF53]/50">

                            {{-- Image Area --}}
                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9]">
                                {{-- Link image (overlay) --}}
                                <a href="{{ route('treatment.show', $item) }}" class="absolute inset-0 z-0 block" aria-label="{{ $item->nama }}">
                                    @if($item->foto_path)
                                        <img src="{{ asset('storage/' . $item->foto_path) }}"
                                             alt="{{ $item->nama }}"
                                             loading="lazy"
                                             class="h-full w-full object-cover transition duration-500 group-hover/card:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                {{-- Badge durasi --}}
                                @if($item->durasi_menit)
                                    <span class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-white/95 backdrop-blur-sm text-[#1A1A1A] px-3 py-1 text-[10px] font-extrabold tracking-widest uppercase shadow-md pointer-events-none">
                                        <svg class="h-3 w-3 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $item->durasi_menit }} mnt
                                    </span>
                                @endif

                                {{-- Hover CTA (sibling) â€” tablet+ saja --}}
                                <div class="hidden sm:block absolute inset-x-3 bottom-3 z-20 opacity-0 translate-y-3 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-300">
                                    <a href="{{ route('appointment.create', ['treatment_id' => $item->treatment_id]) }}"
                                       class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer shadow-lg whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Buat Janji Temu
                                    </a>
                                </div>
                            </div>

                            {{-- Mobile-only CTA bar --}}
                            <a href="{{ route('appointment.create', ['treatment_id' => $item->treatment_id]) }}"
                               class="sm:hidden mx-3 mt-2 mb-3 inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Buat Janji
                            </a>

                            {{-- Body --}}
                            <div class="flex flex-col p-3 sm:p-4 border-t border-[#E8E0D8]/60">
                                <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide text-[#8C7A63] mb-1 sm:mb-1.5">
                                    {{ $item->category->nama ?? 'Treatment' }}
                                </span>

                                <h3 class="text-[13px] sm:text-base font-bold text-[#1A1A1A] line-clamp-2 leading-snug min-h-[2.25rem] sm:min-h-[2.5rem]">
                                    <a href="{{ route('treatment.show', $item) }}" class="hover:text-[#8C7A63] transition-colors">{{ $item->nama }}</a>
                                </h3>

                                <div class="mt-1.5 sm:mt-2">
                                    <span class="text-sm sm:text-lg font-extrabold text-[#CFAF53]">
                                        Rp {{ number_format((int)$item->harga, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </article>
