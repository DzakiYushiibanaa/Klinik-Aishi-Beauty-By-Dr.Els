{{-- Partial: Empty State --}}
                        <div class="col-span-full">
                            <div class="rounded-2xl border border-dashed border-[#E8E0D8] bg-white p-10 sm:p-14 text-center">
                                <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FAF8F5] text-[#CFAF53]">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-extrabold text-[#1A1A1A]">Treatment tidak ditemukan</h3>
                                <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    Coba ubah kata kunci pencarian atau hapus beberapa filter agar hasil lebih banyak muncul.
                                </p>
                                <a href="{{ route('treatment.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold px-5 py-2.5 transition-colors cursor-pointer">
                                    Hapus Semua Filter
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
