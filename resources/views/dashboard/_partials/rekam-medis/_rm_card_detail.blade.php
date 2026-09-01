{{-- Partial: RM Card Detail --}}
                    {{-- â”€â”€â”€ EXPANDED DETAIL â”€â”€â”€ --}}
                    <div x-show="expanded" x-collapse>
                        <div class="px-5 sm:px-6 pb-6 space-y-5 border-t border-[#E8E0D8]/60 pt-5">

                            {{-- 3. HASIL PEMERIKSAAN --}}
                            @if($rm->keluhan || $rm->diagnosa || $rm->tindakan)
                                <div class="bg-[#FAF8F5] border border-[#E8E0D8]/70 rounded-xl p-4 sm:p-5">
                                    <p class="text-[10px] font-bold text-[#CFAF53] uppercase tracking-widest mb-3">Hasil Pemeriksaan</p>
                                    <div class="space-y-3">
                                        @if($rm->keluhan)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Keluhan</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->keluhan }}</p>
                                            </div>
                                        @endif
                                        @if($rm->diagnosa)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Diagnosa</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->diagnosa }}</p>
                                            </div>
                                        @endif
                                        @if($rm->tindakan)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tindakan</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->tindakan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 4. PRODUK REKOMENDASI --}}
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Produk Rekomendasi</p>
                                @if($produkList->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($produkList as $produk)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-100">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                {{ $produk }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400 italic">Tidak ada produk rekomendasi.</p>
                                @endif
                            </div>

                            {{-- 5. CATATAN LANJUTAN --}}
                            @if($rm->catatan_lanjutan)
                                <div class="bg-blue-50/60 border-l-2 border-blue-300 rounded-r-lg pl-4 pr-4 py-3">
                                    <p class="text-[10px] font-bold text-blue-700 uppercase tracking-widest mb-1">Catatan Lanjutan</p>
                                    <p class="text-sm text-blue-900 leading-relaxed">{{ $rm->catatan_lanjutan }}</p>
                                </div>
                            @endif

                            {{-- 6. DOKUMENTASI FOTO --}}
                            @if($fotoSebelum || $fotoSesudah)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Dokumentasi Foto</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @if($fotoSebelum)
                                            <button type="button" @click="lightboxUrl = '{{ $fotoSebelum }}'" class="group relative block rounded-xl overflow-hidden border border-[#E8E0D8] aspect-[4/3] bg-slate-100 cursor-pointer">
                                                <img src="{{ $fotoSebelum }}" alt="Foto sebelum" class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 text-white text-[10px] font-bold uppercase tracking-wider">Sebelum</span>
                                            </button>
                                        @endif
                                        @if($fotoSesudah)
                                            <button type="button" @click="lightboxUrl = '{{ $fotoSesudah }}'" class="group relative block rounded-xl overflow-hidden border border-[#E8E0D8] aspect-[4/3] bg-slate-100 cursor-pointer">
                                                <img src="{{ $fotoSesudah }}" alt="Foto sesudah" class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 text-white text-[10px] font-bold uppercase tracking-wider">Sesudah</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
